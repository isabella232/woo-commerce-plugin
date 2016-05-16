<?php

namespace core;

abstract class Helper {

    protected static $pluginName = 'campaign_monitor_woocommerce';

    public static function getOption($name, $mixed = false){
        $optionName = self::$pluginName . '_' . $name;
        $option = get_option($optionName,$mixed);
        return $option;
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