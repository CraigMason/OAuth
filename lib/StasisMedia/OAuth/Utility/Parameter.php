<?php
namespace StasisMedia\OAuth\Utility;

/**
 * Basic parameter helper
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Connector
 */
class Parameter
{
    /**
     * Combine a number of key/value based parameter arrays
     *
     * @return array combined array
     */
    public static function combineParameters()
    {
        // Get all of the arrays supplied to the argument
        $parameterArrays = func_get_args();

        $parameters = array();

        // Loop through parameterArray
        foreach($parameterArrays as $parameterArray)
        {
            // Loop through each Parameter
            foreach($parameterArray as $key => $value)
            {
                // If the key exists, merge
                if(array_key_exists($key, $parameters))
                {
                    // If scalar, convert to array first
                    if(is_scalar($parameters[$key]))
                    {
                        $parameters[$key] = array($parameters[$key]);
                    }

                    // If the value is also an array, merge
                    if(is_array($value))
                    {
                        $parameters[$key] = array_merge($parameters[$key], $value);
                    } else {
                        $parameters[$key][] = $value;
                    }


                }
                // Paramater does not yet exist. Add scalar
                else
                {
                    $parameters[$key] = $value;
                }
            }
        }

        return $parameters;
    }

    /**
     * Transforms a key/value array of parameters into a HTTP query string.
     * Duplicate name values will be included.
     *
     * @see self::parseQueryParameters()
     *
     * @param array $parameters
     */
    public static function buildQueryString($parameters)
    {
        // If there is nothing to parse, return an empty array
        if( isset($parameters) === false || $parameters === false) return array();

        $pairs = array();

        // Loop through all keys
        foreach($parameters as $parameter => $value)
        {
            if(is_array($value))
            {
                foreach($value as $duplicate)
                {
                    $pairs[] = $parameter . '=' .rawurlencode($duplicate);
                }
            } else {
                $pairs[] = $parameter . '=' .rawurlencode($value);
            }
        }

        return implode('&', $pairs);
    }
}
