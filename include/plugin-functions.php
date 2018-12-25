<?php

defined('ABSPATH') or die;

// ckeck for duplicate
if (!isset($wMyWallet_functions_loaded) or !$wMyWallet_functions_loaded) {
    $wMyWallet_functions_loaded = true;

    function wMyWallet_insert_new_transaction($user_id, $amount, $type, $old_amount, $new_amount, $description = '', $created_at = null,$order_id = 0)
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
            'order_id' => $order_id,
        ];
        try {
            return wMyWallet_DBHelper::insert(wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions', $data);
        } catch (Exception $exception) {
            doLog('wMyWallet_insert_new_transaction failed.' . '$data: ' . json_encode($data) . ' Error: ' . $exception->getMessage());
        }
        return false;
    }

    add_action('woocommerce_cart_calculate_fees', 'wMyWallet_add_grant_discount', 999);
    function wMyWallet_add_grant_discount(WC_Cart $cart)
    {

        //return if product is deposit product or child of that.
        $items = $cart->get_cart();

        foreach ($items as $item_product) {

            // get item product data
            $item_product_data = $item_product;
            $variation_id = $item_product_data['variation_id'];
            $parent_product_id = $item_product_data['product_id'];
            // choose between variation_id
            $product_id = $variation_id > 0 ? $variation_id : $parent_product_id;

            // continue if product is not in deposit products list
            if (in_array($parent_product_id, wMyWallet_Options::get_array('deposit-product-id'))) {
                return;
            }
        }


        $cart_sub_total = $cart->cart_contents_total + $cart->shipping_total;
        $user_balance = wMyWallet_Wallet::getUserWalletAmount(get_current_user_id());

        $discount = min($user_balance, $cart_sub_total);

        // add discount
        if ($discount > 0)
            $cart->add_fee(wMyWallet_discount_title, -1 * $discount);
    }

    add_action('woocommerce_payment_complete', 'wMyWallet_grant_discount_order_status_completed');
    function wMyWallet_grant_discount_order_status_completed($order_id)
    {
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
        $description = "کسر هزینه سفارش {$order_id}"
 ;
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
            , $old_user_balance, $new_user_balance, $description,null,$order_id);
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
                'شارژ کیف پول از طریق خرید کارت شارژ',
                null,
                $order_id
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
        // search for deposit item
        if (count($items) > 0) {
            $deposit_product_id = wMyWallet_Options::get('deposit-product-id');
            /**
             * @item WC_Order_Item_Product
             */
            foreach ($items as $item) {
                if ($item['product_id'] != $deposit_product_id) {
                    wc_add_notice('شارژ کیف پول همراه با خرید محصولات دیگر امکان پذیر نیست. لطفا شارژ و یا اقلام دیگر را حذف کنید.', 'error');
                    break;
                }
            }
        }
    }


    // add referral code to user on register
    add_action('user_register','wMyWallet_user_register_action');
    /**
     * call add_referral_code_to_user function if use special referral code option was set
     * @param $user_id
     */
    function wMyWallet_user_register_action($user_id){
        if(wMyWallet_Options::get('use_special_referral_code')){
            wMyWallet_add_referral_code_to_user($user_id);
        }
    }


}