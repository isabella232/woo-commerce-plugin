<?php
/**
 * Created by PhpStorm.
 * User: SunriseIntegration4
 * Date: 6/30/2016
 * Time: 4:04 PM
 */

namespace core;


class ClientList
{
    protected static $settings = array();
    const name = 'campaign_monitor_woocommerce_client_list';

    public static function add($list, $settings = array())
    {
        $lists = Helper::getOption(self::name);

       if (empty($lists)) $lists = array();


        $lists[$list] = $settings;

        \core\Helper::updateOption(self::name, $lists);
    }

    public static function clear(){
        return Helper::updateOption(self::name, array());
    }

    public static function get($listId = ''){
        $settings = Helper::getOption(self::name);

        if (!empty($listId)){
            if (is_array($settings)){
                foreach ($settings as $id => $setting){
                    if ($id == $listId){
                        return $settings[$listId];
                    }
                }
            }
            return array();
        }


        return $settings;

    }
}