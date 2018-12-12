<?php
/*
Plugin Name:  woo My Wallet
Description:  Add wallet to woocommerce users with basic abilities
*/

require_once 'vendor/autoload.php';

// Activation
register_activation_hook( __FILE__, 'wMyWallet_activation' );
function wMyWallet_activation(){

    // todo | create wallet transactions table if not exists

}

// Deactivation
register_deactivation_hook(__FILE__, 'wMyWallet_deactivation');
function wMyWallet_deactivation(){
    // todo | Remove wallet transactions table if user confirmed this
}


