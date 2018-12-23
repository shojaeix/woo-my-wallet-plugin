<?php
/*
 * Plugin Name:  کیف پول من (wMyWallet)
 * Description:  ایجاد کیف پول برای کاربران ووکامرس،‌ جهت خرید کالا و واریز/برداشت پول
 * Version: 0.9.0 آزمایشی
 * Author: امین شجاعی
 * Text Domain: woo-my-wallet-plugin
 * Domain Path: /languages
 */
/**
 * Root of plugin
 */
define('wMyWallet_ROOT', __DIR__);

defined('ABSPATH') or die;

//require_once 'vendor/autoload.php';
// load include directory files, config first
if (is_file(__DIR__ . '/include/' . 'config.php'))
    require_once(__DIR__ . '/include/' . 'config.php');

$include_files = scandir(__DIR__ . '/include');
if (count($include_files)) {
    foreach ($include_files as $include_file) {
        if ($include_file == '.' || $include_file == '..') continue;
        if (is_file(__DIR__ . '/include/' . $include_file))
            require_once(__DIR__ . '/include/' . $include_file);
    }
}


// add scripts
add_action( 'wp_enqueue_scripts', 'wMyWallet_scripts' );
function wMyWallet_scripts(){
    // load style.css
    wp_enqueue_style( 'wMyWallet_style', plugins_url(wMyWallet_directory_name . '/style.css') );
}

// Activation
register_activation_hook(__FILE__, 'wMyWallet_activation');
function wMyWallet_activation()
{

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
       `order_id` INT UNSIGNED NULL ,
       `description` TEXT NULL ,
        PRIMARY KEY (`id`)
    ) $charset_collate;";

    wMyWallet_DBHelper::delta_query($sql);

    $table_name = wMyWallet_widthrawal_requests_table_name();
    $widthrawal_requests_table_query = '
    CREATE TABLE  IF NOT EXISTS ' . $table_name . ' 
    (  `id` INT NOT NULL AUTO_INCREMENT ,
       `amount` INT UNSIGNED NOT NULL ,
       `user_id` INT UNSIGNED NOT NULL ,
       `created_at` DATETIME NOT NULL ,
       `paid_at` DATETIME NULL ,
       `admin_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL ,
       `status` VARCHAR(20) NOT NULL ,
       `user_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL ,
        PRIMARY KEY (`id`)
    ) ENGINE = MyISAM CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
    ';
    wMyWallet_DBHelper::delta_query($widthrawal_requests_table_query);

}

// Deactivation
register_deactivation_hook(__FILE__, 'wMyWallet_deactivation');
function wMyWallet_deactivation()
{
    // todo | get confirm from user for delete table

    //  Remove wallet transactions table if user confirmed this
    global $wpdb;
    $transactions_table_name = $wpdb->prefix . wMyWallet_DBHelper::prefix . "transactions";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "DROP TABLE IF EXISTS {$transactions_table_name};";

    $wpdb->query($sql);

}



