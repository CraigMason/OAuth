<?php
namespace StasisMedia\OAuth\Credential;

/**
 * Consumer (Client Credential):
 *   A website or application that uses OAuth to access the Service Provider
 *   on behalf of the User.
 *
 * The Consumer key and secret are used in all stages of the OAuth
 * authorization and consumption stages
 *
 * @author  Craig Mason <craig.mason@stasismedia.com>
 * @package OAuth
 * @version 1.0
 */
class Consumer extends Credential
{
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @var String
     */
    private $key;

    /**
     * @return string Consumer key
     */
    public function getKey()
    {
        return $this->key;
    }

}