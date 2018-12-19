<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 15:43
 */

if(!isset($wMyWallet_shortcodes_loaded) or !$wMyWallet_shortcodes_loaded){
    // my wallet transactions page
    add_shortcode('wMyWallet_my_wallet_transactions', 'wMyWallet_show_my_wallet_transactions');
    function wMyWallet_show_my_wallet_transactions()
    {
        $user_id = get_current_user_id();
        $my_transactions = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions
         where user_id=' . $user_id . ' ORDER BY created_at DESC');
        $args = [
            'transactions' => $my_transactions,
        ];
        return wMyWallet_render_template('my_wallet_transactions', $args);
    }

    // my wallet amount
    add_shortcode('wMyWallet_my_wallet_amount', 'wMyWallet_my_wallet_amount');
    function wMyWallet_my_wallet_amount()
    {
        return wMyWallet_Wallet::getUserWalletAmount(get_current_user_id()) . get_woocommerce_currency_symbol();
    }


    // widthrawal request shortcode
    add_shortcode('wMyWallet_show_withdrawal_request_form', 'wMyWallet_show_withdrawal_request_form');
    function wMyWallet_show_withdrawal_request_form()
    {
        // validate input
        $required_fields_list = [
            'amount',
            'transfer_type',
            'cart_number',
            'account_number',
            'user_description',
        ];
        $validated_data = [];
        $validated = false;
        $errors = [];
        // validate amount
        if (!isset($_POST['amount'])) {
            array_push($errors, 'مبلغ وارد نشده است.');
        } else if (!is_numeric($_POST['amount'])) {
            array_push($errors, 'مبلغ وارد شده نامعتبر است.');
        } else {
            $validated_data['amount'] = $_POST['amount'];
        }
        // validate transfer_type
        if (!isset($_POST['transfer_type'])) {
            array_push($errors, 'نوع واریز را انتخاب کنید.');
        } else if (!in_array($_POST['transfer_type'], ['cart_to_cart', 'permanent'])) {
            array_push($errors, 'نوع واریز را انتخاب کنید.');
        } else {
            $validated_data['transfer_type'] = htmlspecialchars($_POST['transfer_type']);
        }
        // validate cart and account number
        if (isset($validated_data['transfer_type'])) {
            switch ($validated_data['transfer_type']) {
                case 'cart_to_cart':
                    if (!isset($_POST['cart_number'])) {
                        array_push($errors, 'شماره کارت وارد نشده است.');
                    } else if (!is_numeric($_POST['cart_number'])) {
                        array_push($errors, 'شماره کارت نامعتبر است.');
                    } else {
                        $validated_data['cart_number'] = $_POST['cart_number'];
                    }
                    break;
                case 'permanent':
                    if (!isset($_POST['account_number'])) {
                        array_push($errors, 'شماره حساب وارد نشده است.');
                    } else if (!is_numeric($_POST['account_number'])) {
                        array_push($errors, 'شماره حساب نامعتبر است.');
                    } else {
                        $validated_data['account_number'] = $_POST['account_number'];
                    }
                    break;
            }
        }


        // validate user_description
        if (isset($_POST['user_description'])) {
            if (!is_string($_POST['user_description'])) {
                array_push($errors, 'توضیحات وارد شده نامعتبر است');
            } else {
                $validated_data['user_description'] = htmlspecialchars($_POST['user_description']);
            }
        }

        $validated = !(bool)count($errors);

        if($validated){
        $withdrawal_min = (int)wMyWallet_Options::get('withdrawal-min');
        if($withdrawal_min>0){
                if ($validated_data['amount'] < $withdrawal_min) {
                    array_push($errors, 'مبلغ درخواست حداقل باید ' . $withdrawal_min . ' باشد.');
                    $validated = false;
                }
            }
        }

        if($validated){
            $wallet = wMyWallet_Wallet::getUserWallet(get_current_user_id());
            if($validated_data['amount'] > $wallet->get_amount()){
                array_push($errors, 'مبلغ درخواست از موجودی کیف پول بیشتر است');
                $validated = false;
            }
        }
        // show form if not validated
        if (!$validated) {
            return wMyWallet_render_template('withdrawal_request_form_for_customer',[
                'errors' => $errors,
            ]);
        }
        // insert new request if validated
        $withdrawal_id = wMyWallet_insert_new_widthrawal_request(get_current_user_id(),
            $validated_data['amount'],
            'pending',
            $validated_data['user_description'],
            null);

        // subtract request amount from wallet amount
        $old_amount = $wallet->get_amount();

        try{
            $wallet->minus_amount($validated_data['amount']);
            $wallet->save();
        } catch (Exception $exception){
            doLog(__FUNCTION__ . ' error in line ' . __LINE__ . ' Error: ' . $exception->getMessage());
        }
        $new_amount = $wallet->get_amount();
        // create transaction
        wMyWallet_insert_new_transaction(get_current_user_id(),$validated_data['amount'],
            'subtraction',
            $old_amount,
            $new_amount,
            'کسر موجودی به موجب درخواست برداشت وجه شماره ' . $withdrawal_id);

        return 'درخواست شما با موفقیت ثبت شد.';

    }

    // my withdrawal requests shortcode
    add_shortcode('wMyWallet_my_withdrawal_requests', 'wMyWallet_my_withdrawal_requests');
    function wMyWallet_my_withdrawal_requests()
    {
        $user_id = get_current_user_id();
        $requests = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_widthrawal_requests_table_name() . ' where user_id=' . $user_id . ' order by created_at desc');

        return wMyWallet_render_template('user_withdrawal_requests', [
            'requests' => $requests,
        ]);
    }


    $wMyWallet_shortcodes_loaded = true;
}