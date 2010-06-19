<?php
namespace StasisMedia\OAuth\Parameter;

/**
 * HTTP Parameter
 *
 * Key/Value pair. Value is an array of values
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Parameter
 */
class Parameter implements \Iterator
{
    private $_key;

    private $_values = array();

    private $_position = 0;

    private $_encoded = false;

    public function __construct($key, $values)
    {
        $this->_position = 0;

        $this->_key = $key;
        $this->addValues((array) $values);
    }

    public function addValue($value)
    {
        $this->addValues((array) $values);
    }

    public function addValues(array $values)
    {
       foreach($values as $value)
       {
           $this->_values[] = $value;
       }
    }

    function current()
    {
        return $this->_values[$this->_position];
    }

    function key()
    {
        return $this->_position;
    }

    function rewind()
    {
        $this->_position = 0;
    }

    function next()
    {
        ++$this->_position;
    }

    function valid()
    {
        return isset($this->_values[$this->_position]);
    }

    public function getJoined()
    {        
        $pairs = array();
        foreach($encoded as $value)
        {
            $pairs[] = rawurlencode($this->_key) . '=' . rawurlencode($value);
        }

        return implode('&', $pairs);
    }

    /**
     * Sorts the values in ascending byte-value order. The values are sorted
     * as if they were percent encoded. Those functions use caches, so should
     * be mighty speedy.
     */
    public function sort()
    {
        usort($this->_values, function($a, $b){
            /* @var $a Value */
            /* @var $b Value */
            return strcmp($a->getPercentEncoded(), $b->getPercentEncoded());
        });
    }
}