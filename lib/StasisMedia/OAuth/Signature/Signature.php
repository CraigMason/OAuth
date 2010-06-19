<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request\RequestInterface;

require_once dirname(__FILE__) . '/../../../php-utf8/utf8.inc';

/**
 * OAuth 1.0 signature base class
 * http://tools.ietf.org/html/rfc5849#section-3.4
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
abstract class Signature implements SignatureInterface
{
    /**
     *
     * @var RequestInterface
     */
    protected $_request;

    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;

        $this->_request->setParameter(
                'oauth_signature_method',
                $this->getSignatureMethod()
        );
    }


    /**
     * Concatenates the request method, base string URI and parameters
     * @return string The full base string
     */
    protected function _getBaseString()
    {
        $parts = array();

        // 1. Request method
        $parts[0] = rawurlencode($this->_getBaseStringRequestMethod());

        // 2. Base string URI
        $parts[1] = rawurlencode($this->_getBaseStringURI());

        // 3. Request parameters
        $parts[2] = rawurlencode($this->_getNormalizedParameters());

        return implode($parts, '&');
    }

    /**
     * Get the HTTP request method (GET, POST, PUT, DELETE or other) from the
     * Request
     *
     * @return string
     */
    private function _getBaseStringRequestMethod()
    {
        return strtoupper($this->_request->getRequestMethod());
    }

    /**
     * Get the base string URI from the Request
     * @return String
     */
    private function _getBaseStringURI()
    {
        return $this->_request->getBaseStringURI();
    }

    /**
     * Proxy method - return the normalized parameters.
     *
     * @return string
     */
    private function _getNormalizedParameters()
    {
        return $this->_normalizeParameters($this->_request->getParameters());
    }

    /**
     * Normalizes the the parameters
     *
     * @param array $parameters
     * @return string
     */
    protected function _normalizeParameters($parameters)
    {
        $encoded = $this->_encodeParameters($parameters);
        $sorted = $this->_sortParameters($encoded);
        $joined = $this->_joinParameters($sorted);

        return implode('&', $joined);
    }

    /**
     * Encodes the key-value pairs according to
     * http://tools.ietf.org/html/rfc5849#section-3.6
     *
     * @param array $parameters
     */
    private function _encodeParameters($parameters)
    {
        array_walk($parameters, array($this, '_encodeKeyValue'));

        return $parameters;
    }

    /**
     * Encodes a key/value pair with utf-8, followed by rawurlencode()
     *
     * @param string $value
     * @param string $key
     */
    private function _encodeKeyValue(&$value, &$key)
    {
        // Only encode strings
        $key = is_string($key) ?: rawurlencode($this->_utf8Encode($key));

        if(is_array($value))
        {
            array_walk($value, array($this, '_encodeKeyValue'));
        }
        elseif(is_string($value))
        {
            $value = urlencode($this->_utf8Encode($value));
        }
    }

    /**
     * Detects whether a string is UTF-8. If not, we will attempt to
     *
     * @param string $string
     * @return string
     */
    private function _utf8Encode($string)
    {
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

    /**
     * Sort the parameters by key, then value if keys match.
     *
     * The strings should be sorted using 'byte value ordering'. However,
     * unlike previously thought, by urlencoding the UTF-8 values, we are
     * only working with ASCII characters. Thus, we can use the regular
     * 'strcmp' function in PHP.
     *
     * http://markmail.org/message/ppzg65eslngpov24
     * http://markmail.org/message/ppzg65eslngpov24
     *
     * @param array $parameters
     */
    private function _sortParameters($parameters)
    {
        // Sort based on keys
        uksort($parameters, 'strcmp');

        // Sort based on values
        array_walk($parameters, function(&$value){
            if(is_array($value)) usort($value, 'strcmp');
        });

        return $parameters;
    }

    /**
     * Joins each key/value pair with '='
     *
     * @param array $parameters  key/value pairs
     */
    private function _joinParameters($parameters)
    {
        $joined = array();
        foreach($parameters as $key => $value)
        {
            $joined[] = $key . '=' . $value;
        }

        return $joined;
    }

}