<?php
/*
Plugin Name:  woo My Wallet
Description:  Add wallet to woocommerce users with basic abilities
*/

require_once 'vendor/autoload.php';
// load include directory files
$include_files = scandir( __DIR__ . '/include' );
if( count($include_files)){
    foreach($include_files as $include_file ){
        if( $include_file == '.' || $include_file == '..' ) continue;
        if( is_file(__DIR__ . '/include/' . $include_file) )
            require_once(__DIR__ . '/include/' . $include_file);
    }
}

// Activation
register_activation_hook( __FILE__, 'wMyWallet_activation' );
function wMyWallet_activation(){

    global $wpdb;
    // create wallet transactions table if not exists
    $transactions_table_name = $wpdb->prefix . wMyWallet_DBHelper::prefix . "transactions";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$transactions_table_name} 
    (  `id` INT NOT NULL AUTO_INCREMENT ,
       `amount` INT UNSIGNED NOT NULL ,
       `type` VARCHAR(20) NOT NULL ,
       `created_at` DATETIME NOT NULL ,
       `user_id` INT UNSIGNED NOT NULL ,
       `old_amount` INT UNSIGNED NOT NULL ,
       `new_amount` INT UNSIGNED NOT NULL ,
        PRIMARY KEY (`id`)
    ) $charset_collate;";

    wMyWallet_DBHelper::delta_query($sql);

}

// Deactivation
register_deactivation_hook(__FILE__, 'wMyWallet_deactivation');
function wMyWallet_deactivation(){
    // todo | get confirm from user for delete table

    //  Remove wallet transactions table if user confirmed this
    global $wpdb;
    $transactions_table_name = $wpdb->prefix . wMyWallet_DBHelper::prefix . "transactions";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "DROP TABLE IF EXISTS {$transactions_table_name};";

    $wpdb->query($sql);

}


