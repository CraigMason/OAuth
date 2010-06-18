<?php
namespace StasisMedia\OAuth\Credential;

/**
 * OAuth 1.0 Request credential
 *
 * Used temporarily when negotiating access with the Service Provider
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Credential
 */
class Request
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