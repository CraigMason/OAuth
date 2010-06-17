<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Exception;
use StasisMedia\OAuth\Request;
use StasisMedia\OAuth\Credential;

/**
 * OAuth 1.0 HMAC SHA1 Signature
 * http://tools.ietf.org/html/rfc5849#section-3.4.2
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Signature
 */
class HMAC_SHA1 extends Signature implements SignatureInterface
{
    const SIGNATURE_METHOD = 'HMAC-SHA1';

    /**
     *
     * @var Credential\Consumer
     */
    private $_consumerCredential;

    /**
     *
     * @var Credential\Access
     */
    private $_accessCredential;
    

    public function __construct(Request\RequestInterface $request)
    {
        parent::__construct($request);

        // Set the other required parameters
        $request->addRequiredOAuthParameters(array(
            'oauth_timestamp',
            'oauth_nonce',
            'oauth_timestamp',
        ));
    }

    /**
     * Returns the 'signature method' for use in oauth_signature_method
     * 
     * @return string the oauth_signature_method
     */
    public function getSignatureMethod()
    {
        return self::SIGNATURE_METHOD;
    }

    /**
     * Generates the signature for the request
     * 
     * @return string The base64 encoded HMAC-SHA1 signature
     */
    public function generateSignature()
    {
        if(false === $this->_request->hasRequiredParameters())
        {
            $missing = $this->_request->getMissingParameters();
            throw new Exception\ParameterException('Missing required oauth_'
                . ' parameters:' . rtrim(implode(', ', $missing), ', '));
        }

        // Build the string
        $parameters = $this->_request->getParameters();
        $normalizedParameters = $this->_normalizeParameters($parameters);

        // $keyString = somsumersecret&tokensecret
        // $signature = hash_hmac('sha1', $base_string, $keyString, true)
    }

    /**
     * Set the Consumer Credential
     *
     * @param Consumer $consumerCredential
     */
    public function setConsumerCredential(Credential\Consumer $consumerCredential)
    {
        $this->_consumerCredential = $consumerCredential;
    }

    /**
     * Set the Access Credential
     * 
     * @param Access $accessCredential
     */
    public function setAccessCredential(Credential\Access $accessCredential)
    {
        $this->_accessCredential = $accessCredential;
    }

}