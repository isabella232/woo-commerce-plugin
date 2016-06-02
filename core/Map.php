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

    public static function get(){
        return Helper::getOption('field_mapping');
    }

    public static function clear(){
        Helper::updateOption('field_mapping', null );
    }
}