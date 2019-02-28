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

    /**
     * get exact key of meta and return all match metas.
     * @param $key string exact key
     * @param $single bool return 1 item on true, return array of items on false
     * @return array of objects|object|null
     */
    public static function get_data($key, $single = true, $value = null){
        // build select query
        $query = "select * from " . wMyWallet_data_table_name() . " where data_key='" . $key . "' ";
        // add value condition
        if(!is_null($value)){
            $query .= " AND data_value='" . $value . "' ";
        }
        // add limit for single meta
        if($single){
            $query .= " LIMIT 1";
        }
        // execute query and get metas
        $metas = self::select($query);
        $count = count($metas);

        // return null if didnt find any match meta
        if(!$count){
            return null;
        }
        // return single meta
        if($single){
            return $metas[0];
        }
        // return all metas
        return $metas;
    }

    /**
     * set data
     */
    public static function set_data($key, $value, bool $insert_only_if_not_exists){
        // return if insert_only)if_not_exists argument is true and key&value exists in db
        if($insert_only_if_not_exists){
            if(self::get_data($key,true, $value)){
                return false;
            }
        }
       // insert
        return (bool)self::wpdb()->insert(wMyWallet_data_table_name(), [
            'data_key' => $key,
            'data_value' => $value,
        ]);
    }

    /**
     * Get metas by type
     * @param $type string
     * @param $single bool
     * @param null $key string
     * @return array of objects|object|null
     */
    public static function get_data_by_type($type, $single = false, $key = null){
        // build select query
        $query = "select * from " . wMyWallet_data_table_name() . " where type='" . $type . "' ";
        // add key condition
        if(!is_null($key)){
            $query .= " AND data_key='" . $key . "' ";
        }
        // add limit for single meta
        if($single){
            $query .= " LIMIT 1";
        }
        // execute query and get metas
        $metas = self::select($query);
        $count = count($metas);

        // return null if didnt find any match meta
        if(!$count){
            return null;
        }
        // return single meta
        if($single){
            return $metas[0];
        }
        // return all metas
        return $metas;
    }

    /**
     * insert new data to data table
     * @param $key
     * @param $value
     * @param null $type
     * @return int
     * @throws Exception
     */
    public static function set_meta($key, $value, $type = null){
        $atts = [
            'key' => $key,
            'value' => $value,
            'type' => $type,
        ];
        return self::insert(wMyWallet_data_table_name(), $atts);
    }
}