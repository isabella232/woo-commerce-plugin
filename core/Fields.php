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

        //print_r($this);
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

    public static function clear()
    {

            global $wpdb;
            $results = $wpdb->get_results( 'DELETE  FROM '.$wpdb->options.' WHERE option_name LIKE "%' . self::name . '%"', OBJECT );

    }

    public static function get_required()
    {

            global $wpdb;
            $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->options.' WHERE option_name LIKE "%' . self::name . '%"', OBJECT );

            $fields = array();
            if (!empty($results)){
                foreach ($results as $result) {
                    $values = unserialize($result->option_value);
                    $isRequired = $values['required'];
                    if (!$isRequired) continue;
                    $fields[] = array('internal_code' => $result->option_name, 'field' => $values);

                }
            }

            return $fields;

    }

    protected static function mapAttributes($key, $value)
    {
        if (is_bool($value)) {
            return $value ? $key : '';
        }
        return $key . '="' . htmlspecialchars($value) . '"';
    }

    public static function get_select($type, $selectedOption = '', $options = array()){
        $fields = self::get();


        $properties = implode(' ',array_map(array(__CLASS__, 'mapAttributes'), array_keys($options), $options ) );

        if (empty($properties)){
            $properties = 'class="dropdown-select"';
        }

        $dateSelect = '<select '.$properties.'><option value="">- Woocommerce fields -</option>';
        $textSelect = '<select '.$properties.'><option value="">- Woocommerce fields -</option>';
        $numberSelect = '<select '.$properties.'><option value="">- Woocommerce fields -</option>';

        foreach ($fields as $item) {
            $field = (object)$item['field'];
            $code = $field->code;

            $selected = '';
            if ($selectedOption == $code){

                    $selected = 'selected="selected"';
            }

            switch ($field->type){
                case 'Number' :
                    $numberSelect .= '<option '.$selected.' value="'.$code.'">';
                    $numberSelect .= $field->name;
                    $numberSelect .= '</option>';
                    break;
                case 'Text' :
                    $textSelect .= '<option '.$selected.' value="'.$code.'">';
                    $textSelect .= $field->name;
                    $textSelect .= '</option>';
                    break;
                case 'Date' :
                    $dateSelect .= '<option '.$selected.' value="'.$code.'">';
                    $dateSelect .= $field->name;
                    $dateSelect .= '</option>';
                    break;
            }
        }
        $dateSelect .= '</select>';
        $textSelect .= '</select>';
        $numberSelect .= '</select>';

        switch ($type){
            case FieldType::NUMBER:
                return $numberSelect;
                break;
            case FieldType::DATE:
                return $dateSelect;
                break;
            case FieldType::TEXT:
                return $textSelect;
                break;
        }

    }
}