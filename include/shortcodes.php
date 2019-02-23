<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 15:43
 */

defined('ABSPATH') or die;

if(!isset($wMyWallet_shortcodes_loaded) or !$wMyWallet_shortcodes_loaded){
    // my wallet transactions page
    add_shortcode('wMyWallet_my_wallet_transactions', 'wMyWallet_show_my_wallet_transactions');
    function wMyWallet_show_my_wallet_transactions()
    {
        $user_id = get_current_user_id();
        // Return empty if user not logged in
        if(!$user_id){
            return '';
        }

        $my_transactions = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions
         where user_id=' . $user_id . ' ORDER BY created_at DESC');

        $count = count($my_transactions);
         for($i=0; $i<$count; $i++){

            if(isset($my_transactions[$i]->order_id) and $my_transactions[$i]->order_id>0)
            {
                try {
                    $order = new WC_Order($my_transactions[$i]->order_id);
                    $my_transactions[$i]->order_link = $order->get_view_order_url();
                } catch (Exception $exception){ }
            }
        }

        $args = [
            'transactions' => $my_transactions,
        ];


        return wMyWallet_render_template('my_wallet_transactions', $args);
    }

    // my wallet amount
    add_shortcode('wMyWallet_my_wallet_amount', 'wMyWallet_my_wallet_amount');
    function wMyWallet_my_wallet_amount()
    {
        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }
        return wMyWallet_Wallet::getUserWalletAmount(get_current_user_id())  . ' ' . get_woocommerce_currency_symbol();
    }


    // widthrawal request shortcode
    add_shortcode('wMyWallet_show_withdrawal_request_form', 'wMyWallet_show_withdrawal_request_form');
    function wMyWallet_show_withdrawal_request_form()
    {

        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }
        $wallet = wMyWallet_Wallet::getUserWallet(get_current_user_id());

       //if($wallet->get_amount() < min((int)wMyWallet_Options::get('withdrawal-min'),0)){
        if($wallet->get_amount() < (int)wMyWallet_Options::get('withdrawal-min')){
            return 'موجودی کیف پول شما کمتر از حداقل مجاز(' . ((int)wMyWallet_Options::get('withdrawal-min')) . ') است.';
        }

        if($wallet->get_amount() <= 0){
            return 'موجودی کیف پول کافی نیست.';
        }

// show form if not validated
        if (!isset($_POST['submit'])) {
            return wMyWallet_render_template('withdrawal_request_form_for_customer',[
                'errors' => [],
            ]);
        }

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
                    if (!isset($_POST['cart_number']) ) {
                        array_push($errors, 'شماره کارت وارد نشده است.');
                    } else if (!is_numeric($_POST['cart_number']) or !wMyWallet_validate_cart_number($_POST['cart_number'])) {
                        array_push($errors, 'شماره کارت نامعتبر است.');
                    } else {
                        $validated_data['cart_number'] = $_POST['cart_number'];
                    }
                    break;
                case 'permanent':
                    if (!isset($_POST['account_number']) ) {
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

        // subtract request amount from wallet amount
        $old_amount = $wallet->get_amount();

        try{
            if($wallet->minus_amount($validated_data['amount']) === false){
                array_push($errors,'امکان کسر این مبلغ از موجودی شما(' . $old_amount . ' ' . wMyWallet_get_currency_symbol() . ') وجود ندارد');
                return wMyWallet_render_template('withdrawal_request_form_for_customer',[
                    'errors' => $errors,
                ]);
            }
            $wallet->save();
        } catch (Exception $exception){
            wMyWallet_log(__FUNCTION__ . ' error in line ' . __LINE__ . ' Error: ' . $exception->getMessage());
        }
        $new_amount = $wallet->get_amount();

        // insert new request
        $withdrawal_id = wMyWallet_insert_new_widthrawal_request(get_current_user_id(),
            $validated_data['amount'],
            'pending',
            $validated_data['user_description'],
            null);

        // create transaction
        wMyWallet_insert_new_transaction(get_current_user_id(),$validated_data['amount'],
            'subtraction',
            $old_amount,
            $new_amount,
            'کسر موجودی به موجب درخواست برداشت وجه شماره ' . $withdrawal_id ,null ,0);

        return 'درخواست شما با موفقیت ثبت شد. شماره درخواست: ' . $withdrawal_id
            . '<br><br>'
            . wMyWallet_render_template('buttons');

    }

    // my withdrawal requests shortcode
    add_shortcode('wMyWallet_my_withdrawal_requests', 'wMyWallet_my_withdrawal_requests');
    function wMyWallet_my_withdrawal_requests()
    {

        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }

        $user_id = get_current_user_id();
        $requests = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_widthrawal_requests_table_name() . ' where user_id=' . $user_id . ' order by created_at desc');

        return wMyWallet_render_template('user_withdrawal_requests', [
            'requests' => $requests,
        ]);
    }

    add_shortcode('wMyWallet_show_my_refferal_code','wMyWallet_show_my_refferal_code');
    function wMyWallet_show_my_refferal_code(){
        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }
        return wMyWallet_get_referral_code(null);
    }

    add_shortcode('wMyWallet_my_referral_link','wMyWallet_get_user_referral_link');
    function wMyWallet_get_user_referral_link(){
        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }
        $url =  wp_registration_url() . '&inviter_code=' . wMyWallet_get_referral_code();
        return '<a href="' . $url .'" >' . 'لینک' . '</a>';
    }

    add_shortcode('wMyWallet_my_referral_url','wMyWallet_get_user_referral_url');
    function wMyWallet_get_user_referral_url(){
        // Return empty if user not logged in
        if(!get_current_user_id()){
            return '';
        }
        return wp_registration_url() . '&inviter_code=' . wMyWallet_get_referral_code();
    }

    //++++ invite friend form
    add_shortcode('wMyWallet_invite_friend_form', 'wMyWallet_show_and_process_invite_friend_form');
    function wMyWallet_show_and_process_invite_friend_form(){
        $args = [];
        $errors = [];
        // verify wp nonce
        if(isset($_REQUEST['_wpnonce']) and wp_verify_nonce( $_REQUEST['_wpnonce'], 'wMyWallet-invite-friend'))
        {
            $validated_data = [];
            // validate name
            if(isset($_POST['wMyWallet_name']) and is_string($_POST['wMyWallet_name'])){
                $name = htmlspecialchars($_POST['wMyWallet_name']);
                // validate name length
                if(strlen($name) > 5) {
                    $validated_data['name'] = $name;
                } else {
                    array_push($errors, 'طول نام شما باید حداقل 5 حرف باشد.');
                }
            }
            // validate friend_phone_number
            if(isset($_POST['wMyWallet_friend_email']) and is_string($_POST['wMyWallet_friend_email'])){

                if(is_email(htmlspecialchars($_POST['wMyWallet_friend_email'])))
                {
                    $validated_data['friend_email'] = htmlspecialchars($_POST['wMyWallet_friend_email']);
                } else {
                    // add error
                    array_push($errors,'ایمیل وارد شده نامعتبر است.');
                }
            }
            // validate friend_phone_number
            if(isset($_POST['wMyWallet_friend_phone_number']) and is_string($_POST['wMyWallet_friend_phone_number'])){
                $phone_number = htmlspecialchars($_POST['wMyWallet_friend_phone_number']);
                // validate phone number structure
                if(wMyWallet_validate_phone_number($phone_number)) {
                    $validated_data['friend_phone_number'] = $phone_number;
                } else {
                    array_push($errors, 'شماره وارد شده نامعتبر است.');
                }
            }

            if(isset($validated_data['name'])){
                // phone number
                if(isset($validated_data['friend_email'])){

                    // validate for send email
                     if(wMyWallet_user_can_send_invite_email_to(get_current_user_id(),$validated_data['friend_email'])){
                         // send mail
                         wMyWallet_send_invite_email($validated_data['name'], $validated_data['friend_email']);
                     } else {
                         array_push($errors, 'امکان ارسال دعوتنامه برای این ایمیل وجود ندارد.');
                     }

                }
                // sms
                if(isset($validated_data['friend_phone_number'])){
                    // validate for send sms
                    if(wMyWallet_user_can_send_invite_sms_to(get_current_user_id(),$validated_data['friend_phone_number'])){
                        // send sms
                        wMyWallet_send_invite_sms($validated_data['name'], $validated_data['friend_phone_number']);
                    } else {
                        array_push($errors, 'امکان ارسال دعوتنامه برای این شماره موبایل وجود ندارد.');
                    }

                }
            }
        }
        // pass args to view
        $args['errors'] = $errors;
        $args['success'] = [];

        wMyWallet_render_template('invite_friend_form',$args, false);
    }
    //---- end invite friend form
    $wMyWallet_shortcodes_loaded = true;
}

function wMyWallet_user_can_send_invite_email_to($user_id, $email) : bool {

    return true;
}

function wMyWallet_user_can_send_invite_sms_to($user_id, $phone_number) : bool {
    return true;
}

function wMyWallet_send_invite_email($name, $friend_email){}

function wMyWallet_send_invite_sms($name, $friend_phone_number){}