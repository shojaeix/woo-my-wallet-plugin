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
            return wMyWallet_DBHelper::insert('transactions', $data);
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

    /**
     * Notice error if deposit product added to cart with other products.
     */
    add_action('woocommerce_checkout_process', 'wMyWallet_validate_cart_before_order');
    add_action('woocommerce_before_cart', 'wMyWallet_validate_cart_before_order');

    function wMyWallet_validate_cart_before_order()
    {

        $items = WC()->cart->get_cart_contents();
        // search for deposit item if items count is more than 1
        if (count($items)) {
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

    $wMyWallet_functions_loaded = true;
}