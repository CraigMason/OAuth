<?php
namespace StasisMedia\OAuth\Credential;

/**
 * Abstract 'Token' credential shared by 'Request' and 'Access' Credentials
 */
abstract class Token extends Credential
{
    /**
     * @var string
     */
    protected $token;

    public function __construct($token, $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}