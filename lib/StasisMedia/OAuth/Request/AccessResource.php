<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Credential\Consumer;
use StasisMedia\OAuth\Credential\Exception;
use StasisMedia\OAuth\Parameter;

/**
 * OAuth 1.0 Access Resource request
 * http://tools.ietf.org/html/rfc5849#section-3.1
 *
 * Used for accessing protected resources
 *
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
class AccessResource extends Request implements RequestInterface
{
    /**
     * Request token Credential containing token and secret
     * @var \StasisMedia\OAuth\Credential\Access
     */
    private $_accessCredentials;


    /**
     * Set the TemporaryAccess credential
     * @param TemporaryAccess $requestCredentials
     */
    public function setAccessCredentials(\StasisMedia\OAuth\Credential\Access $accessCredentials)
    {
        $this->_accessCredentials = $accessCredentials;

        $this->setOAuthParameters(array(
            'oauth_token' => $this->_accessCredentials->getToken()
        ));
    }

/**
     *  Parses the HTTP response and extracts the required parameters
     *
     * @param HTTP $response
     * @return \StasisMedia\OAuth\Parameter\Collection Parameter collection
     */
    public function parseResponse(\StasisMedia\OAuth\Response\HTTP $response)
    {
        $headers = $response->getHeaders();
        $body = $response->getBody();

        return array('header' => $headers, 'body' => $body);
    }


    /**
     * Add the nonce, timestamp and verifier to the parameter list, if they
     * have not already been set externally
     */
    public function prepare()
    {
        $oauthParameters = $this->getOAuthParameters();

        // Nonce
        if($oauthParameters->exists('oauth_none') === false)
        {
            $this->setOAuthParameter('oauth_nonce', $this->_generateNonce());
        }

        // timestamp
        if($oauthParameters->exists('oauth_timestamp') === false)
        {
            $this->setOAuthParameter('oauth_timestamp', $this->_generateTimestamp());
        }

    }
}