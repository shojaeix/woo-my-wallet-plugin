<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:37
 */

defined('ABSPATH') or die;

class wMyWallet_DBHelper
{

    const prefix = 'wMyWallet_';
    /**
     * @var wpdb
     */
    private $wpdb;
    /**
     * @var wMyWallet_DBHelper
     */
    private static $instante1 = null;

    public function __construct()
    {
        $this->get_wpdb();
    }

    /**
     * save global wpdb object in $this object
     */
    private function get_wpdb()
    {
        if (!($this->wpdb instanceof wpdb)) {
            global $wpdb;
            $this->wpdb = $wpdb;
        }
        return $this->wpdb;
    }

    public static function wpdb(){
        return self::instante()->get_wpdb();
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

    public static function select($query){

        $instante = self::instante();

        $wpdb = $instante->wpdb;

        $wpdb->query($query);

        return $wpdb->last_result;
    }

    private static function instante(){
        if(self::$instante1 instanceof wMyWallet_DBHelper){
            return self::$instante1;
        }

        self::$instante1 = new wMyWallet_DBHelper();

        return self::$instante1;
    }

    public static function insert($table_name, array $atts){
        $instante = self::instante();
        $wpdb = $instante->get_wpdb();
        $result = $wpdb->insert($table_name,$atts);
        if($result === false){
            throw new Exception(self::$instante1->wpdb()->last_error);
        }
        return $wpdb->insert_id;
    }

    public static function update($table,array $data,array $where){
        return self::instante()->wpdb()->update($table,$data,$where);
    }

    public static function check_unique_meta_value($value,$meta_name = null,$allowed_user_ids = []) : bool {

        $condition = 'meta_value=\'' . $value . '\'';
        if($meta_name != null){
            $condition .= ' AND meta_name=\'' . $meta_name . '\'';
        }

        $metas = self::select('
        select * from ' . self::wpdb()->prefix . 'usermeta
            where ' . $condition);

        $count = count($metas);
        // true if meta value not found
        if(!$count){
            return true;
        }

        foreach($metas as $meta_object){

            if(!in_array($meta_object->user_id,$allowed_user_ids)){
                return false;
            }
        }
        return true;
    }
}