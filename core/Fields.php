<?php

namespace core;

class Fields
{

    protected static $fields = array();
    const name = 'campaign_monitor_woocommerce_mapped_fields';

    public static function addRange($fields)
    {
        if (is_array($fields)) {
            self::$fields = Helper::getOption(self::name);
            foreach ($fields as $fieldCode => $fieldValue) {
                self::$fields[$fieldCode] = $fieldValue;
                \core\Helper::updateOption(self::name, self::$fields);
            }
        }
    }

    public function __construct($code, $name, $type, $description, $mapping, $isDefault = false, $sort = 0){
        $this->_code = $code;
        $this->_name = $name;
        $this->_type = $type;
        $this->_description = $description;
        $this->_sort = $sort;
        $this->_required = $isDefault;
        $this->_mapping = $mapping;

        $record = array();
        $record['name'] = $this->_code;
        $record['type'] = $this->_type;
        $record['description'] = $this->_description;
        $record['sort'] = $this->_sort;
        $record['required'] = $this->_required;
        $record['mapping'] = $this->_required;

        Helper::updateOption($this->_code, $record);

        print_r($this);
    }
    public static function add($code, $name, $type, $description, $isDefault = false, $sort = 0)
    {
        $record = array();
        $record['code'] = $code;
        $record['name'] = $name;
        $record['type'] = $type;
        $record['description'] = $description;
       // $record['mapping'] = $mapping;
        $record['required'] = $isDefault;
        $record['sort'] = $sort;
     //   $record['rules'] = $rules;

        $fieldCode = self::name . '_' . $code;
        \core\Helper::updateOption($fieldCode, $record);
    }

    public static function get($code = '')
    {
        if (null == $code) {
            global $wpdb;
            $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->options.' WHERE option_name LIKE "%' . self::name . '%"', OBJECT );

            $fields = array();
            if (!empty($results)){
                foreach ($results as $result) {
                    $values = unserialize($result->option_value);
                    $fields[] = array('internal_code' => $result->option_name, 'field' => $values);

                }
            }

            return $fields;
        } else {
            $fieldCode = self::name . '_' . $code;
           return Helper::getOption($fieldCode);
        }

        return null;
    }
}