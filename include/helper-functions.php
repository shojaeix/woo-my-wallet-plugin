<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:35
 */

defined('ABSPATH') or die;

if (!isset($wMyWallet_helper_functions_loaded) or !$wMyWallet_helper_functions_loaded) {

    function wMyWallet_datetime_to_string(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    function wMyWallet_string_to_datetime(string $string): DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s',$string);
    }

    function wMyWallet_datetime_to_helical_string(DateTime $dateTime){
        if(function_exists('jdate'))
        {
            return jdate('Y/m/d H:i:s',$dateTime->getTimestamp());
        }
        return $dateTime->format('Y/m/d H:i:s');
    }

    function wMyWallet_helical($time){
        try {
            if ($time instanceof DateTime) {
                return wMyWallet_datetime_to_helical_string($time);
            }
            return wMyWallet_datetime_to_helical_string(wMyWallet_string_to_datetime($time));
        } catch (Exception $exception){
            wMyWallet_log(__FUNCTION__ . $exception->getMessage());
        }
        return 'date';
    }
    function wMyWallet_log($text)
    {
        // Show log $text on screen if wordpress debug is active
        if(defined('WP_DEBUG') and WP_DEBUG)
        {
            echo "<br>" . 'wMyWallet log("' . $text . '")' . "<br>";
        }
        // open file
        $filename = wMyWallet_ROOT . "/wMyWallet_log.log";
        $fh = fopen($filename, "a");
        // log error and return false if file open failed
        if(!$fh){
            error_log('Could not open ' . $filename . ' file.');
            return false;
        }
        // save text on log file
        fwrite($fh, date("d-m-Y, H:i") . " - $text\n") or die("Could not write file!");
        // close file
        fclose($fh);
        // everything done
        return true;
    }

    function wMyWallet_get_datetime_string_to_show(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
        //return jdate('Y-m-d H:i:s')
    }

    /**
     * Require template from templates directory and pass $args array to it.
     * @param $template_name
     * @param array $args
     * @throws Exception throw if template file not exists. template_name.php file should exist in templates directory.
     */
    function wMyWallet_render_template($template_name, array $args = [], bool $return_output = true)
    {
        if (!is_file(wMyWallet_ROOT . '/templates/' . $template_name . '.php'))
            throw new Exception('Template ' . $template_name . ' not found.');

        if ($return_output) {
            ob_start();
            require wMyWallet_ROOT . '/templates/' . $template_name . '.php';
            return ob_get_clean();
        }
        require wMyWallet_ROOT . '/templates/' . $template_name . '.php';
    }

    function wMyWallet_show_admin_error($text)
    {
        ?>
        <div class="error notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }

    function wMyWallet_show_admin_notice($text)
    {
        ?>
        <div class="updated notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }

    function wMyWallet_show_admin_notice_pan($text)
    {
        ?>
        <div class="update-nag notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }

    function wMyWallet_get_transaction($transaction_id)
    {
        $table_name = wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions';
        $query = 'select * from ' . $table_name . ' where id=' . $transaction_id;

        $rows = wMyWallet_DBHelper::select($query);

        if (count($rows)) {
            return $rows[0];
        }
        return null;
    }

    function wMyWallet_widthrawal_requests_table_name()
    {
        return wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'withdrawal_requests';
    }

    function wMyWallet_insert_new_widthrawal_request($user_id, $amount, string $status, $user_description = null, $admin_description = null, $created_at = null, $paid_at = null)
    {
        // create created_at if is null
        if ($created_at == null) {
            $datetime = new DateTime('now', new DateTimeZone('Asia/Tehran'));
            $created_at = wMyWallet_datetime_to_string($datetime);
        } // convert created at to string if it's Object
        else if ($created_at instanceof DateTime) {
            $created_at = wMyWallet_datetime_to_string($created_at);
        }


        $data = [
            'user_id' => $user_id,
            'amount' => $amount,
            'status' => $status,
            'user_description' => $user_description,
            'admin_description' => $admin_description,
            'paid_at' => $paid_at,
            'created_at' => $created_at,
        ];
        try {
            return wMyWallet_DBHelper::insert(wMyWallet_widthrawal_requests_table_name(), $data);
        } catch (Exception $exception) {
            wMyWallet_log(__FUNCTION__ . ' failed.' . '$data: ' . json_encode($data) . ' Error: ' . $exception->getMessage());
        }
        return false;
    }

    function wMyWallet_get_user_withdrawal_requests($user_id)
    {
        return wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_widthrawal_requests_table_name() . ' where user_id=' . $user_id);
    }

    function wMyWallet_get_all_withdrawal_requests(){
        return wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'withdrawal_requests 
        order by created_at desc');
    }

    function wMyWallet_update_withdrawal_request($id,$data){
        return wMyWallet_DBHelper::update(wMyWallet_widthrawal_requests_table_name(),
            $data,
            [
                    'id' => $id
            ]);
    }

    function wMyWallet_get_currency_symbol(){
        return get_woocommerce_currency_symbol();
    }

    function wMyWallet_shortcode_used_page_link($key){
        return get_page_link(wMyWallet_Options::get($key));
    }
    function wMyWallet_short_code_used_page_exist($key) : bool {
        return (bool)wMyWallet_Options::get($key);
    }

    function wMyWallet_validate_cart_number($card='', $irCard=true) : bool
    {
        $card = (string) preg_replace('/\D/','',$card);
        $strlen = strlen($card);
        if($irCard==true and $strlen!=16)
            return false;
        if($irCard!=true and ($strlen<13 or $strlen>19))
            return false;
        if(!in_array($card[0],[2,4,5,6,9]))
            return false;

        for($i=0; $i<$strlen; $i++)
        {
            $res[$i] = $card[$i];
            if(($strlen%2)==($i%2))
            {
                $res[$i] *= 2;
                if($res[$i]>9)
                    $res[$i] -= 9;
            }
        }
        return (array_sum($res)%10 == 0);
    }

    function wMyWallet_get_transactions_by($field, $value){

        if(is_array($value)){
            $condition = ' where ';
            $colon = false;
            foreach ($value as $id){
                if($colon) { $condition .= ' OR '; }
                else {$colon = true;}

                $condition .= 'user_id=' . $id;
            }

        } else {
            $condition = ' where ' . $field . '=\'' . $value . '\'  ';
        }
            $transactions = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions 
        ' . $condition . '
        order by created_at DESC');

            if(!is_array($transactions)){
                return [];
            }
            $user_ids = [];
            foreach ($transactions as $transaction){
                array_push($user_ids, $transaction->user_id);
            }
            $users = wMyWallet_get_users_info($user_ids);


            for ($i = 0; $i<count($transactions); $i++){
                $transactions[$i]->user = $users[$transactions[$i]->user_id];
            }

            return $transactions;

    }

    function wMyWallet_get_all_transactions()
    {
        $transactions = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . wMyWallet_DBHelper::prefix . 'transactions  
        order by created_at DESC');

        $user_ids = [];
        foreach ($transactions as $transaction){
            array_push($user_ids, $transaction->user_id);
        }
        $users = wMyWallet_get_users_info($user_ids);


        for ($i = 0; $i<count($transactions); $i++){
            $transactions[$i]->user = $users[$transactions[$i]->user_id];
        }

        return $transactions;
    }

    function wMyWallet_get_users_info(array $user_ids){
        $where = '';
        $or = false;
        foreach ($user_ids as $id){
            if ($or) {

                $where .= ' OR ';
            } else {
                $or = true;
            }
            $where .= 'ID=' . $id;
        }
        $rows = wMyWallet_DBHelper::select('
        select * from ' . wMyWallet_DBHelper::wpdb()->prefix . 'users 
        where ' . $where);

        $users = [];
        foreach ($rows as $object){
            $users[$object->ID] = $object;
        }
        return $users;
    }

    /**
     * add referral code to user.
     * @param $user_id
     */
    function wMyWallet_add_referral_code_to_user($user_id, $check_pre_code = true){

        // return pre code if exists
        if ($check_pre_code) {
            // get pre code
            $pre_referral_code = wMyWallet_DBHelper::get_user_meta($user_id, 'referral_code');
            if ($pre_referral_code) {
                return $pre_referral_code;
            }
        }

        // get user object
        $user = get_user_by('ID',$user_id);
        // generate new code
        $new_referral_code = substr($user->user_login,0,3) . $user_id;

        // make code unique
        while (
            // check for unique referral value
            wMyWallet_DBHelper::check_unique_meta_value($new_referral_code,'wMyWallet_referral_code',[$user_id]) == false
            OR // check for be unique in user logins
            get_user_by('login',$new_referral_code) != false
        ){
            $try = (isset($try) ? ++$try : 1);
            // generate new code with more login characters, an random
            $new_referral_code = substr($user->user_login,0,5) . $user_id . rand(1,99)
                // add try number if tryed more than 10 times (only because of bug resistence)
                . (($try>10) ? $try : '');

        }

        // save code
        wMyWallet_DBHelper::save_user_single_meta($user_id,'referral_code',$new_referral_code);
        return $new_referral_code;
    }

    /**
     * get user refferal code with respect to plugin options and current user
     * @param $user_id
     * @return mixed|string
     */
    function wMyWallet_get_referral_code($user_id = null){

        if(wMyWallet_Options::get('use-special-referral-code')){
            // return special referral code
            return wMyWallet_add_referral_code_to_user(((is_null($user_id)) ? get_current_user_id() : $user_id));
        }

        if($user_id == null or $user_id == get_current_user_id()){
            // return current user username
            return get_current_user();
        }
        // get user_login from db
        return get_user_by('ID',$user_id)->user_login;
    }

    function wMyWallet_get_referral_code_user_id($referral_code, $include_usernames = true){

        $codes = wMyWallet_DBHelper::select('
        select user_id from ' . wMyWallet_DBHelper::wpdb()->prefix . 'usermeta 
        where meta_key=\'referral_code\' AND meta_value=\'' . $referral_code . '\'');

        if(count($codes)){
            return $codes[0]->user_id;
        }
        if($include_usernames) {
            $user = get_user_by('login', $referral_code);

            if ($user instanceof WP_User) {
                return $user->ID;
            }
        }

        return null;
    }

    function wMyWallet_charge_invited_user_on_register($user_id,$inviter_id = null){


        $wallet = wMyWallet_Wallet::getUserWallet($user_id);
        $invited_user_first_charge_value = (int)wMyWallet_Options::get('invited-user-first-charge');

        if($invited_user_first_charge_value<=0){
            return;
        }
        $old_amount = $wallet->get_amount();
        // add amount
        try {
            $wallet->add_amount($invited_user_first_charge_value);
            $wallet->save();
        } catch (Exception $exception){
            wMyWallet_log(__FUNCTION__ . ' LINE: ' . __LINE__ . ' error: ' . $exception->getMessage());
            return;
        }

        $new_amount = $wallet->get_amount();
        // insert transaction
        wMyWallet_insert_new_transaction($user_id,$invited_user_first_charge_value,'addition',$old_amount,$new_amount,
            'شارژ هدیه از طرف معرف'); // todo | add inviter name


    }


    function wMyWallet_user_completed_orders($user_id = null) {
        if($user_id == null){
            $user_id = get_current_user_id();
        }
        // Get all customer orders
        $customer_orders = get_posts( array(
            //'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $user_id,
            'post_type'   => 'shop_order', // WC orders post type
            'post_status' => 'wc-completed' // Only orders with status "completed"
        ) );

        return $customer_orders;
    }

    function wMyWallet_is_order_first_user_order($order_id){
        $customer_orders = wMyWallet_user_completed_orders();
        if(isset($customer_orders[0])) // check if array have element
        {
            // return false if any none $order_id order is in array
            foreach ($customer_orders as $order){
                if($order->ID != $order_id){
                    return false;
                }
            }
        }
        return true;
    }

    function wMyWallet_is_order_first_user_real_order($order_id){
        $customer_orders = wMyWallet_user_completed_orders();
        if(isset($customer_orders[0])) // check if array have element
        {
            // return false if any none $order_id order is in array
            foreach ($customer_orders as $order){
                if($order->ID != $order_id and
                    get_post_meta($order->ID,wMyWallet_DBHelper::prefix . 'wallet_deposit_order',true) != true
                ){
                    return false;
                }
            }
        }
        return true;
    }
}