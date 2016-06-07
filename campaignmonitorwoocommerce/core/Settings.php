<?php

namespace core;

class Settings {

    protected static $settings = array();
    const name = 'campaign_monitor_woocommerce_account_settings';

    public static function add($setting, $value)
    {
        self::$settings = Helper::getOption(self::name);
        self::$settings[$setting] = $value;
        \core\Helper::updateOption(self::name, self::$settings);
    }

    public static function get($setting = ''){
        if (null == $setting){
            return Helper::getOption(self::name);
        }else {
            $settings = Helper::getOption(self::name);
            if (!empty($settings)){
                if (array_key_exists($setting, $settings)){
                    return $settings[$setting];
                }
            }
        }

        return null;
    }
}