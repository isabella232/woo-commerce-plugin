<?php

namespace core;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Settings
 * @package core
 *
 * Holds settings for the app
 */
class Settings {

    protected static $settings = array();
    const name = 'campaign_monitor_woocommerce_account_settings';

    /**
     * Add a setting
     * 
     * @param $setting
     * @param $value
     */
    public static function add($setting, $value)
    {
        self::$settings = Helper::getArrayOption(self::name);
        self::$settings[$setting] = $value;
        \core\Helper::updateOption(self::name, self::$settings);
    }

    public static function clear(){
        return Helper::updateOption(self::name, []);
    }

    /**
     * get a specific settings or all of them if no argument is provided
     * 
     * @param string $setting
     * @return array | null
     */
    public static function get($setting = ''){
        if (null == $setting){
            return Helper::getArrayOption(self::name);
        }else {
            $settings = Helper::getArrayOption(self::name);
            if (!empty($settings)){
                if (array_key_exists($setting, $settings)){
                    return $settings[$setting];
                }
            }
        }

        return null;
    }
}