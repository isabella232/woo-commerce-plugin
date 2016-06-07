<?php

namespace core;

class Session
{

    protected $methods = array();
    protected $arguments = array();
    protected static $methodsName = array();

    public function __call($method, $arguments)
    {

        switch (substr($method, 0, 3)) {
            case 'get' :
                return $this->$method;
            case 'set' :
                return $this->$method =  isset($arguments[0]) ? $arguments[0] : null;
        }
        throw new \Exception("Invalid method " . get_class($this) . "::" . $method . "(" . print_r($arguments, true) . ")");
    }

    protected function _normalize($name)
    {
        if (isset(self::$methodsName[$name])) {
            return self::$methodsName[$name];
        }

        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$methodsName[$name] = $result;
        return $result;
    }

    public function __set($name, $value)
    {
        $key = $this->_normalize(substr($name, 3));
        $this->methods[$key] = $value;
    }

    public function __get($name)
    {
        $key = $this->_normalize(substr($name, 3));
        if (array_key_exists($key, $this->methods)) {
            return $this->methods[$key];
        }

        return null;
    }

    /**  As of PHP 5.1.0  */
    public function __isset($name)
    {
        return isset($this->methods[$name]);
    }

    /**  As of PHP 5.1.0  */
    public function __unset($name)
    {
        unset($this->methods[$name]);
    }
}