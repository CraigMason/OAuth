<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Credential\Consumer;
use StasisMedia\OAuth\Credential\Exception;
use StasisMedia\OAuth\Request\AccessResource;
use StasisMedia\OAuth\Parameter;

/**
 * OAuth 1.0 Token Credentials request
 * http://tools.ietf.org/html/rfc5849#section-2.1
 *
 * Used for requesting temporary credentials. Stage 3 in the workflow.
 *
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
class TokenCredentials extends AccessResource implements RequestInterface
{

    /**
     * oauth_verifier sent during token request stage
     * @var string
     */
    private $_oauthVerifier;

    /**
     * Set up the required OAuth parameters
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRequiredOAuthParameters(array(
            'oauth_verifier'
        ));
    }

    /**
     * Sets oauth_verifier, which is
     * @param string $oauthVerifier
     */
    public function setOAuthVerifier($oauthVerifier)
    {
        $this->_oauthVerifier = (string) $oauthVerifier;
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

        // Status?
        if($headers['Status'] != '200 OK')
        {
            throw new \Exception(sprintf(
                'Response status %s',
                $headers['Status']
            ));
        }

        /*
        // Content type?
        if(isset($headers['Content-Type']) == false
           || $headers['Content-Type'] !== 'application/x-www-form-urlencoded')
        {
            throw new \Exception(sprintf(
                'Incorrect header \'Content-Type\'. Expected: %s, actual: %s',
                'application/x-www-form-urlencoded', $headers['Content-Type']
            ));
        }
         */

        // See if all parameters exist
        $collection = Parameter\Collection::fromEntityBody($body, 'application/x-www-form-urlencoded');

        if($collection->exists('oauth_token') === false)
            $this->_throwMissingParameterException('oauth_token');

        if($collection->exists('oauth_token_secret') === false)
            $this->_throwMissingParameterException('oauth_token_secret');

        return $collection;
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

        // Verifier
        if($oauthParameters->exists('oauth_verifier') === false)
        {
            $this->setOAuthParameter('oauth_verifier', $this->_oauthVerifier);
        }

    }

}