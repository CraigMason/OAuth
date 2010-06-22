<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request\RequestInterface;
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

        $this->_request->setOAuthParameter(
                'oauth_signature_method',
                $this->getSignatureMethod()
        );
    }


    /**
     * Concatenates the request method, base string URI and parameters
     * 
     * @return string The full base string
     */
    public function getBaseString()
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
        return $this->_request->getParameters()->getNormalized();
    }

}