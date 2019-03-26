<?php
/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Helper;

class ArrayHelper
{
    protected $data = array();

    /**
     * Arrays constructor.
     * @param array $data
     * @param bool $clone
     */
    public function __construct(&$data, $clone = false)
    {
        if(is_array($data)){
            if($clone)
                $this->data = $data; else
                $this->data = &$data;
        }
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null){
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return integer
     */
    public function getInt($key, $default = null){
        $value = $this->get($key, $default);
        return intval($value);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return boolean
     */
    public function getBool($key, $default = null){
        $value = $this->get($key, $default);
        return boolval($value);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string
     */
    public function getString($key, $default = null){
        $value = $this->get($key, $default);
        return (string)$value;
    }

    /**
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getArray($key, $default = array()){
        $value = $this->get($key, $default);
        return is_array($value) ? $value : $default;
    }
}