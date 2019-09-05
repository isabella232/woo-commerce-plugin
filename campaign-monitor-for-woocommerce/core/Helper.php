<?php

namespace core;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Helper
 * @package core
 */
abstract class Helper {

    protected static $pluginName = 'campaign_monitor_woocommerce';

    /**
     * prefixes $name with the plugin prefix
     * @param $name
     */
    public static function getPrefix($name = ''){
        if (empty($name)){
            return self::$pluginName;
        } else {
             return self::$pluginName . '_' . $name;
        }

    }


    /**
     * @return int
     */
    public static function getMaximumFieldsCount(){
        return 50;
    }

    /**
     * wrapper for the wordpress options
     *
     * @param $name
     * @param bool $mixed
     * @return mixed|void
     */
    public static function getOption($name, $mixed = false){
        $optionName = self::$pluginName . '_' . $name;
        $option = get_option($optionName,$mixed);
        return $option;
    }

    /**
     * wrapper for the wordpress options
     *
     * @param $name
     * @param array $default
     * @return default|[]
     */
    public static function getArrayOption( $name, $default = []){
        $optionName = self::$pluginName . '_' . $name;
        $option = get_option($optionName,$default);
        if (!is_array($option)) {            
            $option = [];
        }
        return $option;
    }

    /**
     * @param $name
     */
    public static function deleteOption($name){
        $optionName = self::$pluginName . '_' . $name;
        delete_option($optionName);
    }

    /**
     * @return string
     */
    public static function getActionUrl(){
       return get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce';
    }

    /**
     * @return string
     */
    public static function getCampaignMonitorPermissions()
    {
        $permissions = array("ViewReports", "ViewSubscribersInReports",
            "ManageLists", "ImportSubscribers", "AdministerAccount");

        return implode(',', $permissions);
    }

    /**
     * @return string
     */
    public static function getRedirectUrl(){
       return get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce&connected=true';
    }

    /**
     * wordpress option wrapper
     *
     * @param $name
     * @param string $value
     * @param string $deprecated
     * @param string $autoload
     */
    public static function addOption($name, $value = '', $deprecated = '', $autoload = 'yes' ){
        $optionName = self::$pluginName . '_' . $name;
        add_option($optionName, $value, $deprecated, $autoload  );
    }

    /**
     * wordpress option wrapper
     *
     * @param $name
     * @param $value
     * @param null $autoload
     * @return bool
     */
    public static function updateOption($name, $value, $autoload = null ){
        $optionName = self::$pluginName . '_' . $name;
        return update_option($optionName, $value, $autoload);
    }

    /**
     * @param $file
     */
    public static function renderer($file){

        $filePath = dirname(__DIR__) . '/views/admin/' . $file . '.php';

        if (file_exists($filePath)){
            require_once($filePath);
        }
    }

    /**
     * @param string $file
     * @param bool $url
     * @return string
     */
    public static function getPluginDirectory($file = '', $url = false){
        if (empty($file)){
            return CAMPAIGN_MONITOR_WOOCOMMERCE_DIR;
        } else {
            return CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . $file;
        }
    }

    /***
     * @param $data
     * @param bool $dump
     */
    public static function display($data, $dump = false){
        echo "<pre>";
        if ($dump){
            var_dump($data);
        } else {
            print_r($data);
        }
        echo "</pre>";
    }
}