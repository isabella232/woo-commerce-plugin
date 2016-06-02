<?php

namespace core;


class Rule
{
    protected $_fieldCode = '';
    protected $_clauses = array();
    protected $_sort = 0;


    public function __construct($fieldCode, $clauses, $sort = 0)
    {
        $this->_fieldCode = $fieldCode;
        $this->_clauses = $clauses;
        $this->_sort = $sort;
    }

    public function setCode($fieldCode){
        $this->_fieldCode = $fieldCode;
    }

    public function getCode(){
        return $this->_fieldCode;
    }

    public function setClauses($clause){
        $this->_clauses = $clause;
    }

    public function getClauses(){
        return $this->_clauses;
    }

}