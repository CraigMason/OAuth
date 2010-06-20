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
    /**
     * The key is actually of type Value, as it must be percent encoded too
     * @var Value
     */
    private $_key;

    /**
     * Array of Value objects
     * @var Array
     */
    private $_values = array();

    private $_position = 0;


    public function __construct($key, $values)
    {
        $this->_position = 0;

        $this->_key = new Value($key);
        $this->addValues((array) $values);
    }

    public function addValue($value)
    {
        $this->addValues((array) $value);
    }

    public function addValues(array $values)
    {
       foreach($values as $value)
       {
           $this->_values[] = new Value($value);
       }
    }

    /**
     * Get the current Value object
     * @return Value
     */
    function current()
    {
        return $this->_values[$this->_position];
    }

    /**
     * Just a numeric index, only used when iterating
     * @return <type>
     */
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

    /**
     * Returns the normalised string for this parameter. This differs from
     * a traditional 'join', as text values will have been converted into UTF-8
     * first, which may not be expected.
     */
    public function getNormalized()
    {        
        $pairs = array();
        foreach($this->_values as $value)
        {
            /* @var $value Value */

            $pairs[] = $this->_key->getPercentEncoded() . '=' . $value->getPercentEncoded();
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