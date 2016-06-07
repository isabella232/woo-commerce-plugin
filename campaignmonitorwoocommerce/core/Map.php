<?php
namespace core;


class Map
{
    public static function add($field, $mappedField){

        $map = array();
        $map = Helper::getOption('field_mapping');
        $map[$field] = $mappedField;
        Helper::updateOption('field_mapping', $map );

    }

    public static function remove($field, $removeByValue = false){

        $map = array();
        $map = Helper::getOption('field_mapping');

        if ($removeByValue){
           $index = array_search($field, $map );
            Log::write($field);
            Log::write($index);
            if (!empty($index)){
                if (array_key_exists($index,$map )){
                    unset($map[$index]);
                }
            }

        } else {
            if (array_key_exists($field, $map)){
                unset($map[$field]);
            }
        }

        Helper::updateOption('field_mapping', $map );

    }

    public static function get(){
        return Helper::getOption('field_mapping');
    }

    public static function clear(){
        Helper::updateOption('field_mapping', null );
    }
}