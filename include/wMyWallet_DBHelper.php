<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:37
 */

class wMyWallet_DBHelper
{

    const prefix = 'wMyWallet_';
    private $wpdb;
    /**
     * @var wMyWallet_DBHelper
     */
    private static $instante;

    public function __construct()
    {
        $this->wpdb();
    }

    /**
     * save global wpdb object in $this object
     */
    private function wpdb()
    {
        if (!$this->wpdb instanceof wpdb) {
            global $wpdb;
            $this->wpdb = $wpdb;
        }
    }

    /**
     * @param $user_id
     * @param $meta_key
     * @param $meta_value
     * @param bool $single_meta meta will replace with new value
     * @param null $prev_value
     */
    public static function save_user_single_meta($user_id, $meta_key, $meta_value, $add_prefix = true)
    {
        if($add_prefix){
            $meta_key = 'wMyWallet_' . $meta_key;
        }
        return update_user_meta($user_id, $meta_key, $meta_value);
    }

    /**
     * @param $user_id
     * @param $meta_key
     * @param bool $single_meta
     * @return mixed
     */
    public static function get_user_meta($user_id, $meta_key, $add_prefix = true)
    {
        if($add_prefix){
            $meta_key = 'wMyWallet_' . $meta_key;
        }
        return get_user_meta($user_id, $meta_key, true);
    }


    public static function delta_query(string $sql){

        $instante = self::instante();
        $wpdb = $instante->wpdb;


        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        return dbDelta( $sql );
    }

    private static function instante(){
        if(self::$instante instanceof self){
           return self::$instante;
        }

        self::$instante = new self();

        return self::$instante;
    }
}