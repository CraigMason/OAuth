<?php
namespace StasisMedia\OAuth\Parameter;

/**
 * Collection of HTTP Parameter objects
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Parameter
 */
class Collection
{
    private $_parameters = array();

    public function add($name, $values)
    {
        $name = (string) $name;

        $values = (array) $values;

        // Create Parameter if it does not exist
        if(!$this->exists($name))
        {
            $this->_parameters[$name] = new Parameter($name, $values);
        }
        else {
            $this->get($name)->addValues($values);
        }
    }

    /**
     *
     * @param string $name
     * @return Parameter
     */
    public function get($name)
    {
        return $this->exists($name) ? $this->_parameters[$name] : null;
    }

    public function getAll()
    {
        return $this->_parameters;
    }

    public function exists($name)
    {
        return array_key_exists($name, $this->_parameters);
    }

    private function sort()
    {
        usort($this->_parameters, function($a, $b){
            /* @var $a Parameter */
            /* @var $b Parameter */
            return strcmp(
                $a->getName()->getPercentEncoded(),
                $b->getName()->getPercentEncoded()
            );
        });
    }

    /**
     * @return string OAuth normalized string
     */
    public function getNormalized()
    {
        $this->sort();
        $pairs = array();
        foreach($this->_parameters as $parameter)
        {
            /* @var $parameter Parameter */
            $pairs[] = $parameter->getNormalized();
        }

        return implode('&', $pairs);
    }
}