<?php

//if((function_exists('is_amin') and is_amin()))
 {
 // add referral code to user on register
    add_action('user_register', 'wMyWallet_user_register_action');
    /**
     * call add_referral_code_to_user function if use special referral code option was set
     * @param $user_id
     */
    function wMyWallet_user_register_action($user_id)
    {
        // if use special referral code option was set
        if (wMyWallet_Options::get('use-special-referral-code')) {
            // call add_referral_code_to_user function
            wMyWallet_add_referral_code_to_user($user_id);
        }
    }


    add_action('register_form', 'wMyWallet_add_input_to_register_form');
    function wMyWallet_add_input_to_register_form()
    {
        $inviter_code = '';
        try {
            // get from POST input
            $inviter_code = (isset($_POST['inviter_code']) ? $_POST['inviter_code'] : '');
            // get from url
            if (!strlen($inviter_code)) {
                $inviter_code = (isset($_GET['inviter_code']) ? $_GET['inviter_code'] : '');
            }
            // save to session
            if (strlen($inviter_code)) {
                $_SESSION['inviter_code'] = $inviter_code;
                $_SESSION['inviter_code_timestamp'] = time();
            }
            // get from seassion
            else if(session_id()){
                if (isset($_SESSION['inviter_code_timestamp']) and (time() - $_SESSION['inviter_code_timestamp']) < 3600) {
                    $inviter_code = $_SESSION['inviter_code'];
                }
            }
        } catch (Exception $exception){
            wMyWallet_log(__FUNCTION__ . ' line:' . __LINE__ . $exception->getMessage());
        }
        // create description text base on options
        $first_charge_value = (int)wMyWallet_Options::get('invited-user-first-charge');
        if($first_charge_value){
            $description = '(در صورتی که کد معرف داشته باشید، ' . $first_charge_value . ' ' . wMyWallet_get_currency_symbol() . ' تومان شارژ رایگان دریافت خواهید کرد)';
        } else {
            $description = '';
        }
        ?>
        <p>
            <label for="inviter_code">کد معرف<?php echo $description; ?><input type="text" name="inviter_code" id="wMyWallet_inviter_code"
                                                  class="input" value="<?php echo $inviter_code; ?>"/></label>
        </p>
        <?PHP
    }

    add_filter('registration_errors', 'wMyWallet_register_form_validation_filter', 10, 3);
    function wMyWallet_register_form_validation_filter($errors, $sanitized_user_login, $user_email)
    {

        // check if entered inviter code is actually belongs to any user except "admin" user
        if (isset($_POST['inviter_code']) and strlen($_POST['inviter_code'])) {
                $referral_code_user_id = wMyWallet_get_referral_code_user_id($_POST['inviter_code'],true);


            if (is_null($referral_code_user_id)) {
                $errors->add('inviter_code_error', 'کد معرف یافت نشد.');
             }
            // check if entered inviter code is "admin"
            else if($_POST['inviter_code'] == 'admin'){
                $errors->add('inviter_code_error', 'کد معرف وارد شده غیر فعال است.');
             }
            else if (!wMyWallet_had_user_any_real_order($referral_code_user_id)){
                        $errors->add('inviter_code_error', 'کد معرف  غیر فعال است.
                        (معرف باید تجربه حداقل یکبار همکاری با ما را داشته باشد)
                        ');
            }
            
            //---- apply wooMyWallet_inviter_code_validation filter
            $errors = apply_filters('wMyWallet_inviter_code_validation', $errors, $_POST['inviter_code']);
            //----


        }

        // check if entered username is not a referral code
        if(wMyWallet_get_referral_code_user_id($sanitized_user_login,false)){
            $errors->add('invalid_user_login', 'لطفا از نام کاربری دیگری استفاده کنید.');
        }
        return $errors;
    }

    add_action('user_register', 'wMyWallet_process_inviter_code_on_register');
    function wMyWallet_process_inviter_code_on_register($user_id)
    {
        if (isset($_POST['inviter_code']) and
            (is_string($_POST['inviter_code']) or is_numeric($_POST['inviter_code'])) and
            strlen($_POST['inviter_code'])) {
            $inviter_code = htmlspecialchars($_POST['inviter_code']);


            // check for code existentse
            $inviter = wMyWallet_get_referral_code_user_id($inviter_code);
            if ($inviter == null) {
                return;
            }
            // check if user is not self inviter
            if ($inviter_code == $user_id) {
                return;
            }
            // save inviter id
            wMyWallet_DBHelper::save_user_single_meta($user_id, 'inviter', $inviter, false);
            wMyWallet_DBHelper::save_user_single_meta($user_id, 'entered_inviter_code', $inviter_code, false);

            wMyWallet_charge_invited_user_on_register($user_id, $inviter);
        }
    }


    add_action( 'woocommerce_order_status_completed', 'wMyWallet_award_inviter_on_user_first_order', 10);
    function wMyWallet_award_inviter_on_user_first_order($order_id){
        $order = new WC_Order( $order_id );
        $user_id = $order->user_id;

        $is_user_first_order = (bool)wMyWallet_is_order_first_user_real_order($order_id,$user_id);

        //wMyWallet_log('Order ' . $order_id . ' completed' . ' is user first real order: ' . ((int)$is_user_first_order));

        if($is_user_first_order){
            // get award value
            $award_value = (int)wMyWallet_Options::get('inviter-award-on-user-first-order');
            // return if award value is zero
            if($award_value <= 0 ){
                return false;
            }



            // get inviter user id
            $inviter_user_id = wMyWallet_DBHelper::get_user_meta($user_id,'inviter',false);
            // return if user doesn't invited
            if(!$inviter_user_id){
                return false;
            }
             $inviter_wallet = wMyWallet_Wallet::getUserWallet($inviter_user_id);

            $old_amount = $inviter_wallet->get_amount();
            // add award and return false if addition failed
            if(!$inviter_wallet->add_amount($award_value)){
                return false;
            }
            // save changes
            $inviter_wallet->save();
            $new_amount = $inviter_wallet->get_amount();
             // insert new transaction
            wMyWallet_insert_new_transaction($inviter_user_id,$award_value,'addition',$old_amount,$new_amount,'مژدگانی شما به دلیل اولین خرید زیر مجموعه');

        }

    }

}
