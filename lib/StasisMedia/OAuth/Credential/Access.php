<?php
namespace StasisMedia\OAuth\Credential;

/**
 * OAuth 1.0 Access credential
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Credential
 */
class Access
{
    /**
     * Access token
     * @var string
     */
    private $_token;

    /**
     * Access secret
     * @var string
     */
    private $_secret;


    public function __construct($token = null, $secret = null)
    {
        $this->_token = $token;
        $this->_secret = $secret;
    }


    /**
     * Access token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Access token
     * @param string $token
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * Access secret
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * Access secret
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
    }
}