<?php
namespace StasisMedia\OAuth\Parameter;

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

    public function __construct($value)
    {
        $this->set($value);
    }

    public function set($value)
    {
        if(!is_scalar($value))
        {
            throw new Exception(sprintf('Value must be scalar. %s supplied',  gettype($value)));
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
     * Detects whether a string is UTF-8. If not, we will attempt to convert
     * it to UTf-8
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
        
        // auto detects from ASCII,JIS,UTF-8,EUC-JP,SJIS
        $encoding = mb_detect_encoding($string);

        // If we cannot detect the encoding, throw an exception
        if($encoding === false)
        {
            throw new Exception\ParameterException(sprintf(
                    'Encoding of stringcould not be detected: ',
                    $string
            ));
        }

        /*
         * If we can detect the encoding, and it is not UTF-8, convert it.
         * The application should supply pre encoded parameters, but the
         * specification appears to indicate that they should be encoded at
         * encoding time.
         * ASCII,JIS,UTF-8,EUC-JP,SJIS
         */
        if($encoding != 'UTF-8')
        {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return $string;
    }
}