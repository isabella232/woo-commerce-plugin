<?php
namespace core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper class to map fields to segments on campaign monitor
 * Class Map
 * @package core
 */
class Map
{
    /**
     * @param $field
     * @param $mappedField
     */
    public static function add($field, $mappedField){

        $map = array();
        $map = Helper::getOption('field_mapping');
        $map[$field] = $mappedField;
        Helper::updateOption('field_mapping', $map );

    }

    /**
     * @param $field
     * @param bool $removeByValue
     */
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

    /**
     * @param bool $visibleOnly
     * @return mixed|void
     */
    public static function get($visibleOnly = false){
        $mappedFields = Helper::getOption('field_mapping');
        if (!$visibleOnly){
            return $mappedFields;
        } else {
            $returnFieldsOnly = true;
            $hiddenFields = Fields::get_hidden($returnFieldsOnly, 'code');

            if (!empty($hiddenFields)){
                foreach ($hiddenFields as $field){
                    if (array_key_exists($field,$mappedFields )){
                        unset($mappedFields[$field]);
                    }
                }
            }
            return $mappedFields;
        }
    }

    public static function clear(){
        Helper::updateOption('field_mapping', null );
    }
}