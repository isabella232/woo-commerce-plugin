<?php

namespace core;

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

    public static function getMaximumFieldsCount(){
        return 50;
    }

    public static function getOption($name, $mixed = false){
        $optionName = self::$pluginName . '_' . $name;
        $option = get_option($optionName,$mixed);
        return $option;
    }

    public static function getActionUrl(){
       return get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce';
    }

    public static function addOption($name, $value = '', $deprecated = '', $autoload = 'yes' ){
        $optionName = self::$pluginName . '_' . $name;
        add_option($optionName, $value, $deprecated, $autoload  );
    }

    public static function updateOption($name, $value, $autoload = null ){
        $optionName = self::$pluginName . '_' . $name;
        return update_option($optionName, $value, $autoload);
    }

    public static function renderer($file){

        $filePath = dirname(__DIR__) . '/views/admin/' . $file . '.php';

        if (file_exists($filePath)){
            require_once($filePath);
        }
    }

    public static function getPluginDirectory($file = '', $url = false){
        if (empty($file)){
            return CAMPAIGN_MONITOR_WOOCOMMERCE_DIR;
        } else {
            return CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . $file;
        }
    }

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