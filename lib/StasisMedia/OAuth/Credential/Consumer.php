<?php
namespace StasisMedia\OAuth\Credential;

/**
 * OAuth 1.0 Consumer credential
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Credential
 */
class Consumer
{
    /**
     * Consumer key
     * @var string
     */
    private $_key;

    /**
     * Consumer secret
     * @var string
     */
    private $_secret;

    
    /**
     * Consumer key
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Consumer key
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * Consumer secret
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * Consumer secret
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
    }
}