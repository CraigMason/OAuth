<?php
namespace StasisMedia\OAuth\Parameter;

/**
 * HTTP Parameter
 *
 * Name/Value pair. Name can only be set during construction.
 * A value must be passed, but can be blank.
 *
 * Value is an array of Value objects
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Parameter
 */
class Parameter implements \Iterator
{
    /**
     * The name is actually of type Value, as it must be percent encoded too
     * @var Value
     */
    private $_name;

    /**
     * Array of Value objects
     * @var Array
     */
    private $_values = array();

    private $_position = 0;


    public function __construct($name, $values)
    {
        $this->_position = 0;

        $this->_name = new Value($name);
        $this->addValues((array) $values);
    }

    public function __tostring()
    {
        return $this->getNormalized();
    }

    /**
     *
     * @return Value
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Removes all values and sets single value
     * 
     * @param string
     */
    public function reset($value)
    {
        unset($this->_values);
        
        $this->addValue($value);
    }

    /**
     * Adds a value as a duplicate
     * 
     * @param string $value
     */
    public function addValue($value)
    {
        $this->addValues((array) $value);
    }

    /**
     * Adds values as duplicates
     * 
     * @param array $values
     */
    public function addValues(array $values)
    {
       foreach($values as $value)
       {
           $this->_values[] = new Value($value);
       }
    }

    /**
     * All values in an array
     * @return Array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Returns the normalised string for this parameter. This differs from
     * a traditional 'join', as text values will have been converted into UTF-8
     * first, which may not be expected.
     *
     * @return string
     */
    public function getNormalized()
    {
        $this->sort();
        $pairs = array();
        foreach($this->_values as $value)
        {
            /* @var $value Value */

            $pairs[] = $this->_name->getPercentEncoded() . '=' . $value->getPercentEncoded();
        }

        return implode('&', $pairs);
    }

    /**
     * Sorts the values in ascending byte-value order. The values are sorted
     * as if they were percent encoded. Those functions use caches, so should
     * be mighty speedy.
     */
    private function sort()
    {
        usort($this->_values, function($a, $b){
            /* @var $a Value */
            /* @var $b Value */
            return strcmp($a->getPercentEncoded(), $b->getPercentEncoded());
        });
    }

    /*
     * Iterator implementation
     */

    /**
     * Get the current Value object
     * @return Value
     */
    public function current()
    {
        return $this->_values[$this->_position];
    }

    /**
     * Just a numeric index, only used when iterating
     * @return <type>
     */
    public function key()
    {
        return $this->_position;
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function next()
    {
        ++$this->_position;
    }

    function valid()
    {
        return isset($this->_values[$this->_position]);
    }

}