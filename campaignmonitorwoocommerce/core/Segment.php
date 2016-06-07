<?php
/**
 * Created by PhpStorm.
 * User: SunriseIntegration4
 * Date: 5/25/2016
 * Time: 5:27 PM
 */

namespace core;


class Segment implements \ArrayAccess
{
    protected $_title = '';
    protected $_rules = null;

    private $container = array();
    protected $_data = array();
    public function offsetExists ($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    public function offsetGet ($offset)
    {
        return $this->_data[$offset];
    }

    public function offsetSet ($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetUnset ($offset)
    {
        unset($this->_data[$offset]);
    }
    public function __construct($title, $rules)
    {
        $this->_title = $title;
        $this->_rules = $rules;
    }

    public function setTitle($title){
        $this->_title = $title;
    }

    public function getTitle(){
        return $this->_title;
    }

    public function setRules($rules){
        $this->_rules = $rules;
    }
    public function getRules(){
        return $this->_rules;
    }

    // ArrayAccess methods

    public function &toArray() {

        $ruleGroups = array();
        foreach ($this->_rules as $rule) {
            $clauses = $rule->getClauses();
            $code = $rule->getCode();
            $rules = array();
            foreach ($clauses as $clause){
                $rules['Rules'][] = array('RuleType' => $code, 'Clause' => $clause);
            }

            $ruleGroups[] = $rules;
        }

        $object = array('Title' => $this->_title, 'RuleGroups' => $ruleGroups);
        return $object;
    }

}