<?php
namespace StasisMedia\OAuth\Parameter;

/**
 * A parameter ca
 */
class Parameter
{
    /**
     * @var string
     */
    private $name;

    /**
     * Will always be an array
     * @var array
     */
    private $value;

    public function  __construct($name, $value) {
        $this->name = $name;
        $this->setValue($value);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value(s). Test MUST be UTF-8 encoded
     * @param mixed $value scalar or Array of scalars
     */
    public function setValue($value)
    {
        if(is_scalar($value))
        {
            $value = array($value);
        }

        $this->value = $value;
    }

    /**
     * Add a single value to the Parameter
     * @param mixed $value 
     */
    public function addValue($value)
    {
        if(!is_scalar($value))
        {
            throw new Exception('Only scalar values permitted');
        }
        
        $this->value[] = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Encode the UTF-8 encoded string (or raw binary) with percent encoding
     *
     * http://tools.ietf.org/html/rfc5849#section-3.6
     *
     * @return string
     */
    public function getNormalized()
    {
        // Take a copy of our raw values
        $values = $this->value;

        // Percent encode all values
        array_walk($values, function(&$v){
            rawurlencode($v);
        });

        // Sort them using ascending byte-value ordering
        usort($values, function($a, $b){
            return strcmp($a, $b);
        });

        
        // Join the results up
        $name = rawurlencode($this->name);
        $pairs = array();
        array_map(function($value) use ($name, &$pairs){
             $pairs[] = $name . '=' . rawurlencode($value);
        }, $values);
        return implode($pairs, '&');
    }

    /**
     * Parse a key/value array into an array of Parameter objects
     *
     * @param array $array
     * @return array of Parameter objects
     */
    public static function fromArray($array)
    {
        $parameters = array();

        foreach($array as $name => $value)
        {
            $parameters[] = new Parameter($name, $value);
        }

        return $parameters;
    }

    /**
     * Parse a HTTP query string into an array of Parameter objects
     *
     * @param array $array
     * @return array of Parameter objects
     */
    public static function fromQueryString($queryString)
    {
        $parameters = array();

        $pairs = explode('&', $queryString);

        foreach($pairs as $pair)
        {
            $split = explode('=', $pair, 2);
            $split[1] = isset($split[1]) ? $split[1] : '';

            // Decode both name and value
            \array_walk($split, function(&$var){$var = \urldecode($var);});

            // Is this a duplicate?
            if(\array_key_exists($split[0], $parameters))
            {
                $parameters[$split[0]]->addValue($split[1]);
            } else {
                $parameters[$split[0]] = new Parameter($split[0], $split[1]);
            }
        }

        return $parameters;
    }
    

}