<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:25
 */


class wMyWallet_Wallet
{
    private $user_id;

    function __construct($user_id = null)
    {
        $this->user_id = $user_id;
    }

    function set_amount(int $number)
    {
        if ($number < 0) {
            return false;
        }

        $this->amount = $number;
    }

    /**
     * return amount(Toman)
     */
    function get_amount()
    {
        $this->get_amount_from_db_if_is_not_set();
        return $this->amount;
    }

    private function get_amount_from_db_if_is_not_set()
    {
        if (!isset($this->amount)) {
            $this->get_amount_from_db();
        }
    }

    function get_amount_from_db()
    {
        $this->amount = (int)wMyWallet_DBHelper::get_user_meta($this->user_id, 'amount');
        return $this->amount;
    }

    function get_amount_as_rial()
    {
        return $this->amount * 10;
    }

    function add_amount(int $number)
    {
        if ($number < 1) {
            return false;
        }

        $this->get_amount_from_db_if_is_not_set();

        $this->amount = $this->amount + $number;

        return $this->amount;
    }

    function minus_amount(int $number)
    {
        if ($number < 1) {
            return false;
        }

        $this->get_amount_from_db_if_is_not_set();

        $new_amount = $this->amount - $number;

        if ($new_amount < 0) {
            return false;
        }
        $this->amount = $new_amount;

        return $this->amount;
    }

    function save()
    {
        if (isset($this->amount)) {
            return wMyWallet_DBHelper::save_user_single_meta($this->user_id, 'amount', $this->amount);
        } else {
            return false;
        }

    }

    public static function getUserWallet($user_id) : self {
        return new self($user_id);
    }
    public static function getUserWalletAmount($user_id){
        return self::getUserWallet($user_id)->get_amount();
    }
}