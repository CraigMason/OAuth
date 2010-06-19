<?php
namespace StasisMedia\OAuth\Parameter;

use StasisMedia\OAuth\Exception;

/**
 * HTTP Parameter Value
 *
 * Value for a parameter. Can only be scalar
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Parameter
 */
class Value
{
    private $_value;

    /*
     * Caches
     */
    private $_percentEncoded;
    private $_utf8Encoded;
    private $_encoding = 'UTF-8';

    public function __construct($value)
    {
        $this->set($value);
    }

    /**
     * A UTF-8 string, or number. If other encoding is used, specify with
     * setEncoding()
     *
     * @param string $value
     */
    public function set($value)
    {
        if(!is_scalar($value))
        {
            throw new \Exception(sprintf('Value must be scalar. %s supplied',  gettype($value)));
        }
        $this->_value = $value;
        $this->_percentEncoded = null;
        $this->_utf8Encoded = null;
    }

    public function get()
    {
        return $this->_value;
    }

    public function __toString()
    {
        return $this->get();
    }

    /**
     * Set the value encoding. Legal values are those used by `iconv`. Use
     * `iconv -l` to see valid encodings.
     *
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
    }

    /**
     * Encode the UTF-8 encoded string (or raw binary) with percent encoding
     *
     * http://tools.ietf.org/html/rfc5849#section-3.6
     */
    public function getPercentEncoded()
    {
        // Use the cache?
        if(!isset($this->_percentEncoded))
        {
            $this->_percentEncoded = rawurlencode($this->getUtf8Encoded());
        }
        
        return $this->_percentEncoded;
    }

    /**
     * Encodes the string as UTF-8.
     *
     * @param string $string
     * @return string UTF-8 encoded string
     */
    public function getUtf8Encoded()
    {
        // Use the cache?
        if(isset($this->_utf8Encoded)) return $this->_utf8Encoded;

        // If this is not a string, don't encode it
        if(!is_string($this->_value)) return $this->_value;

        $string = $this->_value;        
        
        if($this->_encoding != 'UTF-8')
        {
            $string = iconv($this->_encoding, 'UTF-8', $string);
            if($string === false)
            {
                throw new \Exception(sprintf(
                    'Invalid encoding (%s), or conversion error',
                    $this->_encoding
                ));
            }
        }

        return $string;
    }
}