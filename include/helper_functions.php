<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:35
 */

if (!isset($wMyWallet_helper_functions_loaded) or !$wMyWallet_helper_functions_loaded) {

    function wMyWallet_datetime_to_string(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    function wMyWallet_string_to_datetime(string $string) : DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s');
    }

}