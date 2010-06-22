<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Credential\Consumer;
use StasisMedia\OAuth\Credential\Exception;

/**
 * OAuth 1.0 Temporary Credentials request
 * http://tools.ietf.org/html/rfc5849#section-2.1
 *
 * Used for requesting temporary credentials. Stage 1 in the workflow.
 *
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
class TemporaryCredentials extends Request implements RequestInterface
{
    /**
     * A Consumer credential containing key and secret
     * @var Consumer
     */
    private $_consumerCredentials;

    /**
     * The callback URL the Provider will call
     * @var string
     */
    private $_callbackUrl;

    /**
     * Set up the required OAuth parameters
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRequiredOAuthParameters(array(
            'oauth_callback'
        ));
    }

    /**
     * Set the Consumer Credential
     * @param Consumer $consumerCredentials
     */
    public function setConsumerCredentials(Consumer $consumerCredentials)
    {
        $this->_consumerCredentials = $consumerCredentials;

        
        $this->setOAuthParameter(
            'oauth_consumer_key',
            $this->_consumerCredentials->getKey()
        );
    }

    /**
     * The callback URL the Provider will call
     * 
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->_callbackUrl = $callbackUrl;

        $this->setOAuthParameter(
            'oauth_callback',
            $this->_callbackUrl
        );
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
            throw new Exception(sprintf(
                'Response status %s',
                $response['headers']['Status']
            ));
        }

        // Content encoding?
        if($headers['Content-Encoding'] !== 'application/x-www-form-urlencoded')
        {
            throw new Exception(
                'Response Content-Encoding must be \'application/x-www-form-urlencoded\''
            );
        }

        // See if all parameters exist
        $collection = Parameter\Collection::fromQueryString($body);

        if($collection->exists('oauth_token') === false) 
            $this->_throwMissingParameterException('oauth_token');

        if($collection->exists('oauth_token_secret') === false)
            $this->_throwMissingParameterException('oauth_token_secret');

        if($collection->exists('oauth_callback_confirmed') === false)
            $this->_throwMissingParameterException('oauth_callback_confirmed');

        return $collection;
    }

    /**
     * Throws an Exception for a missing response parameter
     * @param string $parameter The missing parameter
     */
    protected function _throwMissingParameterException($parameter)
    {
        throw new \Exception\Parameter(sprintf(
            'Required response parameter absent: \'%s\'',
            $parameter
        ));
    }
}
