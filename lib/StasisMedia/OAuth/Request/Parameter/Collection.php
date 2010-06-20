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

    /**
     * Add a key/value pair. Value can be an array of values
     * 
     * @param string $name
     * @param string|array $values String or array of string values
     */
    public function add($name, $values)
    {
        $name = (string) $name;
        if(empty($name)) throw new Exception\ParameterException('Name cannot be empty');

        $values = (array) $values;

        // Create Parameter if it does not exist
        if(!$this->exists($name))
        {
            $this->_parameters[$name] = new Parameter($name, $values);
            $this->_sort();
        }
        else {
            $this->get($name)->addValues($values);
        }
    }

    /**
     * Reset a single parameter/value pair to the supplied values
     *
     * @param string $name
     * @param string $value
     */
    public function reset($name, $value)
    {
        if($this->exists($name))
        {
            $this->get($name)->reset($value);
        } else {
            $this->add($name, $value);
        }
    }

    /**
     *
     * @param string $name
     * 
     * @return Parameter
     */
    public function get($name)
    {
        return $this->exists($name) ? $this->_parameters[$name] : null;
    }

    /**
     * Return all parameters as Parameter objects
     *
     * @return array
     */
    public function getAll()
    {
        return $this->_parameters;
    }

    /**
     * Get the key values of the parameters array, which match the 'name'
     * property of each Value
     *
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->_parameters);
    }

    /**
     *
     * @param string $name Value name property
     * 
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->_parameters);
    }

    private function _sort()
    {
        uasort($this->_parameters, function($a, $b){
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
        $this->_sort();
        $pairs = array();
        foreach($this->_parameters as $parameter)
        {
            /* @var $parameter Parameter */
            $pairs[] = $parameter->getNormalized();
        }

        return implode('&', $pairs);
    }

    /**
     * Parse the query-string of a URI into an associative array. Duplicate
     * keys will transform the parameter into an array
     *
     * @return Collection
     */
    public static function fromQueryString($queryString)
    {
        // If there is nothing to parse, return an empty array
        if( isset($queryString) === false || $queryString === false) return null;

        // New collection
        $collection = new \StasisMedia\OAuth\Parameter\Collection();

        // Split the key pairs with an ampersand
        $pairs = explode('&', $queryString);

        foreach($pairs as $pair)
        {
            $split = explode('=', $pair, 2);

            $name = rawurldecode($split[0]);
            // Value may be blank
            $value = isset($split[1]) ? rawurldecode($split[1]) : '';

            $collection->add($name, $value);
        }

        return $collection;
    }

    /**
     * Parse the Authorization header content. Scheme will be removed
     *
     * @param string $header
     * 
     * @return Collection
     */
    public static function fromAuthorizationHeader($header)
    {
        if(empty($header)) return null;

        $collection = new \StasisMedia\OAuth\Parameter\Collection();

        // Get the header, and remove the 'OAuth ' auth-scheme part
        $header = preg_replace('/^\w+\s/', '', $header);

        $parts = preg_split('/,\s?/', $header);
        foreach($parts as $part)
        {
            $pair = explode('=', $part, 2);

            // Do NOT include the 'realm' parameter
            if($pair[0] === 'realm') continue;

            $collection->add(
                rawurldecode($pair[0]),
                rawurldecode(trim($pair[1], '"'))
            );
        }

        return $collection;
    }

}