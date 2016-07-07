<?php

namespace core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * People who have checked the subscribed option
 *
 * Class Subscribers
 * @package core
 */
class Subscribers {

    protected static $subscribers = array();
    const name = 'campaign_monitor_woocommerce_account_subscribers';

    /**
     * @param $subscriber
     */
    public static function add($subscriber)
    {
        $s = Helper::getOption(self::name);
        if (empty($s)){
            $s = array();
        }
        array_push($s, $subscriber);
        self::$subscribers =  array_unique($s);
        \core\Helper::updateOption(self::name, self::$subscribers);
    }

    /**
     * @return bool
     */
    public static function clear(){
        return Helper::updateOption(self::name, null);
    }

    /**
     * @param string $subscriber
     * @return mixed|null|void
     */
    public static function get($subscriber = ''){
        if (null == $subscriber){
            return Helper::getOption(self::name);
        }else {
            $subscribers = Helper::getOption(self::name);
            if (!empty($subscribers)){
                if (in_array($subscriber, $subscribers)){
                    $old = array_flip($subscribers);
                    $index = $old[$subscriber];
                    return $subscribers[$index];
                }
            }
        }

        return null;
    }
}