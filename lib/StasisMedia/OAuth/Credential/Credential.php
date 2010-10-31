<?php
namespace StasisMedia\OAuth\Credential;

/**
 * There are 3 types of credentials:
 *
 * Consumer Key and Secret (client)
 * Request Token and Secret (temporary)
 * Access Token and Secret (token)
 */
abstract class Credential
{
    /**
     * @var String
     */
    protected $secret;

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }
}
