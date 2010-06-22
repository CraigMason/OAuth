<?php
namespace StasisMedia\OAuth\Response;

/**
 * OAuth 1.0 HTTP Response
 *
 * Basic data object for a HTTP response
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
class HTTP
{
    /**
     *
     * @var array
     */
    private $_headers;

    /**
     * @var string
     */
    private $_body;

    
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }
    
    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setBody($body)
    {
        $this->_body = $body;
    }

    public function getBody()
    {
        return $body;
    }
}