<?php

// ckeck for duplicate
if (!isset($wMyWallet_functions_loaded) or !$wMyWallet_functions_loaded) {

    function wMyWallet_insert_new_transaction($user_id, $amount, $type, $old_amount, $new_amount, $description = '', $created_at = null)
    {
        // create created_at if is null
        if ($created_at == null) {
            $datetime = new DateTime('now', new DateTimeZone('Asia/Tehran'));
            $created_at = wMyWallet_datetime_to_string($datetime);
        } // convert created at to string if it's Object
        else if ($created_at instanceof DateTime) {
            $created_at = wMyWallet_datetime_to_string($created_at);
        }
        // validate type
        if (!in_array($type, ['subtraction', 'addition'])) {
            throw new Exception('invalid type');
        }

        $data = [
            'user_id' => $user_id,
            'amount' => $amount,
            'type' => $type,
            'old_amount' => $old_amount,
            'new_amount' => $new_amount,
            'description' => $description,
            'created_at' => $created_at,
        ];
        try {
            return wMyWallet_DBHelper::insert(wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions', $data);
        } catch (Exception $exception) {
            doLog('wMyWallet_insert_new_transaction failed.' . '$data: ' . json_encode($data) . ' Error: ' . $exception->getMessage());
        }
        return false;
    }

    function wMyWallet_get_wallet_transactions()
    {
    }

    function wMyWallet_get_all_transactions()
    {
    }


    /*
     function wMyWallet_update_member_balance($user_id, $transaction_id)
    {
        // get post meta
        $amount = get_post_meta($transaction_id, 'amount', TRUE);
        $type = get_post_meta($transaction_id, 'type', TRUE);
        $balance = wMyWallet_Wallet::getUserWalletAmount($user_id);
        // calc new balance
        $new_balance = ($type == 'add') ? $balance + $amount : $balance - $amount;
        // update balance
        update_user_meta($user_id, 'wMyWallet_balance', $new_balance);
    }
     */

    /*
     * Get wMyWallet balance from user metas
     * @param $user_id
     * @return int

    function wMyWallet_get_user_balance($user_id)
    {
        $balance = wMyWallet_DBHelper::get_user_meta($user_id, 'balance');
        return (int)$balance;
    }
    */

    add_action('woocommerce_cart_calculate_fees', 'wMyWallet_add_grant_discount', 999);
    function wMyWallet_add_grant_discount($cart)
    {

        $cart_sub_total = $cart->cart_contents_total + $cart->shipping_total;
        $user_balance = wMyWallet_Wallet::getUserWalletAmount(get_current_user_id());

        $discount = min($user_balance, $cart_sub_total);

        // return if product is deposit product or child of that.
        // add discount
        if ($discount > 0)
            $cart->add_fee(wMyWallet_discount_title, -1 * $discount); // todo | replace text with constant
    }

    add_action('woocommerce_payment_complete', 'wMyWallet_grant_discount_order_status_completed');
    function wMyWallet_grant_discount_order_status_completed($order_id)
    {
        doLog(__FUNCTION__ . ' line: ' . __LINE__);
        $order = new WC_Order($order_id);
        // find discount field in order fees and add to $grant_used
        $order_fees = $order->get_fees();
        $grant_used = 0;
        foreach ($order_fees as $order_fee) {
            $order_fee_data = $order_fee->get_data();
            if ($order_fee_data['name'] == wMyWallet_discount_title)
                $grant_used += $order_fee->get_total();
        }
        // return if discount not found or it's zero
        if ($grant_used == 0) return;
        //create transaction
        $amount = $grant_used * -1;
        $description = "کسر هزینه سفارش: <a href='"
            . get_bloginfo('url')
            . "/my-account/view-order/{$order_id}'>{$order_id}</a>";
        $member_id = get_current_user_id();
        /*
        $post = array(
            'post_content' => $description,
            'post_title' => $member_id . "_substract_" . $amount . "_" . wMyWallet_get_datetime_string_to_show(new DateTime()),
            'post_type' => 'wMyWallet_transaction',
            'post_status' => 'publish',
            'meta_input' => array(
                'type' => 'subtract',
                'amount' => $amount,
                'member' => $member_id
            )
        );
        $transaction_id = wp_insert_post($post);
        wMyWallet_update_member_balance($member_id, $transaction_id);
        update_post_meta($transaction_id, 'balance', wMyWallet_get_member_balance($member_id));
        */
        // get user wallet
        $wallet = wMyWallet_Wallet::getUserWallet($member_id);
        //  old balance
        $old_user_balance = $wallet->get_amount();
        // subtract discount from wallet amount
        if ($wallet->minus_amount($amount) === false) {
            // todo | cancel order and add paid amount to wallet
            doLog('discount subtract failed.');
            return false;
        }
        $wallet->save();
        //  new balance
        $new_user_balance = $wallet->get_amount();

        wMyWallet_insert_new_transaction($member_id, $amount, 'subtraction'
            , $old_user_balance, $new_user_balance, $description);


        doLog(__FUNCTION__ . ' line: ' . __LINE__);
    }

    // deposit by deposit-product
    add_action('woocommerce_payment_complete', 'wMyWallet_add_deposit_by_product');
    function wMyWallet_add_deposit_by_product($order_id)
    {
        //$order = new WC_Order($order_id);
        $order = wc_get_order($order_id);
        $items = $order->get_items();

        $deposit_amount = 0;
        /**
         * @var WC_Order_Item_Product
         */
        foreach ($items as $item_product) {

            // get item product data
            $item_product_data = $item_product->get_data();
            $variation_id = $item_product_data['variation_id'];
            $parent_product_id = $item_product_data['product_id'];
            $product_id = $variation_id > 0 ? $variation_id : $parent_product_id;


            // continue if product is not in deposit products list
            if (!in_array($parent_product_id, wMyWallet_Options::get_array('deposit-product-id'))) {
                continue;
            }

            // get Product and product data
            $product = wc_get_product($product_id);
            $product_data = $product->get_data();
            $regular_price = $product_data['regular_price'];
            $quantity = $item_product_data['quantity'];

            // calc deposit amount
            $deposit_amount += $regular_price * $quantity;
        }

        if ($deposit_amount > 0) {

            $order_user_id = $order->get_user_id();
            // add amount to wallet
            $wallet = wMyWallet_Wallet::getUserWallet($order_user_id);
            $old_amount = $wallet->get_amount();
            try {
                $wallet->add_amount($deposit_amount);
                $wallet->save();
            } catch (Exception $exception) {
                doLog('error in wallet deposit. Order id = ' . $order_id . ' Error: ' . $exception->getMessage());
            }
            $new_amount = $wallet->get_amount();
            // insert new transaction
            wMyWallet_insert_new_transaction(
                $order_user_id,
                $deposit_amount,
                'addition',
                $old_amount,
                $new_amount,
                'شارژ کیف پول از طریق سفارش شماره ' . $order_id
            );
        }

    }

    /**
     * Notice error if deposit product added to cart with other products.
     */
    add_action('woocommerce_checkout_process', 'wMyWallet_validate_cart_before_order');
    add_action('woocommerce_before_cart', 'wMyWallet_validate_cart_before_order');

    function wMyWallet_validate_cart_before_order()
    {

        $items = WC()->cart->get_cart_contents();
        // search for deposit item if items count is more than 1
        if (count($items) > 1) {
            $deposit_product_id = wMyWallet_Options::get('deposit-product-id');
            /**
             * @item WC_Order_Item_Product
             */
            foreach ($items as $item) {
                if ($item['product_id'] == $deposit_product_id) {
                    wc_add_notice('شارژ کیف پول همراه با خرید محصولات دیگر امکان پذیر نیست. لطفا چیز کنید.', 'error');
                }
            }
        }
    }

    // my wallet transactions page
    //add_shortcode('wMyWallet_my_wallet_transactions','wMyWallet_show_my_wallet_transactions');
    function wMyWallet_show_my_wallet_transactions(){
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
    add_shortcode('wMyWallet_my_wallet_amount','wMyWallet_my_wallet_amount');
    function wMyWallet_my_wallet_amount(){
        return '1000 تومان';
    }

    // new transaction page
    //add_shortcode('wmywallet_new_transaction_page','wmywallet_new_transaction_page');
    function wmywallet_new_transaction_page(){
        if(!isset($_GET['user_id']) or !is_numeric($_GET['user_id'])){
            //wMyWallet_show_admin_error('ایدی کاربر وارد نشده است.');
            return wMyWallet_render_template('new_transction_choose_user_form',[] , false);

        }
        $user_id = $_GET['user_id'];

        $user = get_user_by('ID',$user_id);

        if($user === false){
            wMyWallet_show_admin_error('ایدی کاربر نامعتبر است.');
            return wMyWallet_render_template('new_transction_choose_user_form',[] , false);

        }
        $args = [
            'user' => $user,
        ];

        // validation
        $validated = true;
        if(!isset($_POST['amount']) or !is_numeric($_POST['amount']) or (int)htmlspecialchars($_POST['amount']) <= 0){
            if(isset($_POST['amount']))
            wMyWallet_show_admin_error('مقدار تراکنش نامعتبر است.');
            $validated = false;
        }

        if(!isset($_POST['type']) or !is_string($_POST['type']) or !in_array($_POST['type'], ['subtraction', 'addition'])){
            if(isset($_POST['type']))
            wMyWallet_show_admin_error('نوع تراکنش نامعتبر است.');
            $validated = false;
        }

        if(!isset($_POST['description']) or !is_string($_POST['description']) or strlen($_POST['description']) < 20){
            if(isset($_POST['description']))
            wMyWallet_show_admin_error('لطفا حداقل ۲۰ حرف به عنوان توضیحات وارد کنید.');
            $validated = false;
        }


        // show form if not validated
        if(!$validated)
        {
            return wMyWallet_render_template('new_transaction_form',$args, false);
        }

        $validated_data = [
            'amount' => $_POST['amount'],
            'type' => $_POST['type'],
            'description' => $_POST['description'],
        ];


        // confirm
        $confirm = false;
        if(isset($_POST['confirm']) and is_numeric($_POST['confirm']) and (int)$_POST['confirm'] === 2){
            $confirm = true;
        }
        // show confirm page if not confirmed
        $args['validated_data'] = $validated_data;

        $wallet = new wMyWallet_Wallet($user->ID);
        $args['wallet_amount'] = $wallet->get_amount();

        if(!$confirm){
            return wMyWallet_render_template('new_transaction_confirm',$args, false); // show confirm page
        }
        // created new transaction if confirmed

        $old_amount = $wallet->get_amount();
        try{

            if($validated_data['type'] == 'subtraction') {
                if ($wallet->minus_amount($validated_data['amount']) == false) {
                    wMyWallet_show_admin_error('موجودی کیف پول کاربر کافی نیست.' . '(' . $wallet->get_amount() . ')');
                    return wMyWallet_render_template('new_transaction_form', $args, false); // show confirm page
                }
            } else if ($validated_data['type'] == 'addition') {
                if ($wallet->add_amount($validated_data['amount']) == false) {
                    wMyWallet_show_admin_error('خطا در هنگام افزایش موجودی کیف پول.');
                    return wMyWallet_render_template('new_transaction_confirm', $args, false); // show confirm page
                }
            } else {
                throw new Exception('function: ' . __FUNCTION__ . ' LINE: ' . __LINE__);
            }

            $wallet->save();
        } catch (Exception $exception){
            wMyWallet_show_admin_error($exception->getMessage());
        }
        $new_amount = $wallet->get_amount();
        // insert new transaction
        $transaction_id = wMyWallet_insert_new_transaction(
            $user->ID,
            $validated_data['amount'],
            $validated_data['type'],
            $old_amount,
            $new_amount,
            $validated_data['description']
        );

        $transaction = wMyWallet_get_transaction($transaction_id);
        $args = [
            'user' => $user,
            'transaction' => $transaction,

        ];

        wMyWallet_show_admin_notice('تراکنش با موفقیت انجام شد.');
        // show new transaction info
        return wMyWallet_render_template('transaction_info',$args, false);
    }

    // return transaction info
    function wMyWallet_transaction_info(){
        if(!isset($_GET['transaction_id']) or !is_numeric($_GET['transaction_id'])){
            // show error only if entered transaction id  is invalid
            if(isset($_GET['transaction_id']))
            {
                wMyWallet_show_admin_error('شناسه تراکنش نامعتبر است.');
            }

            return wMyWallet_render_template('transaction_info_form',[],false);

        }

        $transaction_id = $_GET['transaction_id'];

        $transaction = wMyWallet_get_transaction($transaction_id);
        if(is_null($transaction)){
            wMyWallet_show_admin_error('شناسه تراکنش نامعتبر است.');

           return wMyWallet_render_template('transaction_info_form',[],false);
        }
        return wMyWallet_render_template('transaction_info',[
            'user' => get_user_by('id',1),
            'transaction' => $transaction,
        ],false);
    }
    $wMyWallet_functions_loaded = true;
}