<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/16/2018
 * Time: 10:32
 */

class wMyWallet_Options
{
    /**
     * @var array
     */
    private static $data = [];

    private static $options_list = [
        'deposit-product-id' => 'string',
        'withdrawal-min' => 'string',
    ];
    private static $default_values = [
        'deposit-product-id' => 0,
        'withdrawal-min' => 20000,
    ];

    private static $exist_options_in_db = [];
    private static $loaded = false;

    /**
     * Get value of key
     * @param $key string
     * @param $only_if_saved bool On true, default value will disregard
     * @return string|array|null Saved value if exists, Or default value if isset or null
     */
    public static function get(string $key, bool $only_if_saved = false)
    {
        if (!self::$loaded) {
            self::load_all_options_from_db();
        }

        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }

        if (!$only_if_saved and isset(self::$default_values[$key])) {
            return self::$default_values[$key];
        }

        return null;
    }

    /**
     * Save new value for key in db
     * @note Avoid repetition
     * @param $key string
     * @param $value
     */
    public static function set(string $key, $value)
    {
        if(self::get($key) == $value){
            return true;
        }

        self::$data[$key] = $value;

        if (!isset(self::$exist_options_in_db[$key])) {
            $exists = !is_null(self::get_option_from_db($key));
        } else {
            $exists = self::$exist_options_in_db[$key];
        }

        $table_name = wMyWallet_DBHelper::wpdb()->prefix . 'options';
        $option_name = wMyWallet_DBHelper::prefix . $key;
        $atts = [

            'option_value' => self::cast_option_to_string($key, $value),

        ];
        if (!$exists) {
            $atts['option_name'] = $option_name;
            $atts['autoload'] = 'no';
            return (bool)wMyWallet_DBHelper::insert($table_name, $atts);
        } else {
            $where = [
                'option_name' => $option_name,
            ];
            return (bool)wMyWallet_DBHelper::update($table_name, $atts, $where);
        }
    }

    /**
     * Load all options from db which in self::options_list
     */
    private static function load_all_options_from_db()
    {
        //return true if options list is empty
        if (!count(self::$options_list)) {
            return true;
        }
        self::$loaded = true;
        // build query
        $query = '';
        $table_name = wMyWallet_DBHelper::wpdb()->prefix . 'options';
        $query .= "Select * from $table_name where ";
        $condition = '';
        $and = false;
        foreach (self::$options_list as $key => $type) {
            // add " and "
            if ($and) {
                $condition .= ' OR ';
            } else {
                $and = true;
            }

            $condition .= 'option_name=' . "'" . wMyWallet_DBHelper::prefix . $key . "'";
        }
        $query .= $condition;
        // execute query and get result
        $result = wMyWallet_DBHelper::select($query);

        if (count($result)) {
            $plugin_prefix_length = strlen(wMyWallet_DBHelper::prefix);
            foreach ($result as $object) {
                // remove prefix
                $option_key = substr($object->option_name, $plugin_prefix_length);
                // cast and put to self::$data
                self::$data[$option_key] = self::cast_option($option_key, $object->option_value);
                self::$exist_options_in_db[$option_key] = true;
            }
            return true;
        }
        return false;
    }

    /**
     * Cast option value to it's original type( read type from self::$options_list )
     * @param $key
     * @param $value
     * @return mixed
     */
    private static function cast_option($key, $value)
    {
        if (self::$options_list[$key] == 'array' and is_string($value)) {
            return unserialize($value);
        }
        return $value;
    }

    /**
     * Cast option value to string for save in db
     * @param $key
     * @param $value
     * @return string
     */
    private static function cast_option_to_string($key, $value)
    {
        if (self::$options_list[$key] == 'array' and !is_string($value)) {
            return serialize($value);
        }
        return $value;
    }

    private static function get_option_from_db($key)
    {
        $table_name = wMyWallet_DBHelper::wpdb()->prefix . 'options';
        $option_name = wMyWallet_DBHelper::prefix . $key;
        $query = "Select * from $table_name where option_name='$option_name'";

        $result = wMyWallet_DBHelper::select($query);

        if (count($result)) {
            self::$exist_options_in_db[$key] = true;
            return self::cast_option($key, $result[0]->option_value);
        }
        self::$exist_options_in_db[$key] = false;
        return null;
    }
}