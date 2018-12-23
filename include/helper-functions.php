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
            doLog(__FUNCTION__ . $exception->getMessage());
        }
        return 'date';
    }
    function doLog($text)
    {
        echo "<br>" . 'doLog("' . $text . '")';
        $filename = "dolog.log";
        $fh = fopen($filename, "a") or die("Could not open log file.");
        fwrite($fh, date("d-m-Y, H:i") . " - $text\n") or die("Could not write file!");
        fclose($fh);
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
            doLog(__FUNCTION__ . ' failed.' . '$data: ' . json_encode($data) . ' Error: ' . $exception->getMessage());
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

}