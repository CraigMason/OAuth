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
     * Generates the signature for the request.
     *
     * Will throw an Exception if any required parameter or Credential
     * is missing
     * 
     * @return string The base64 encoded HMAC-SHA1 signature
     */
    public function generateSignature()
    {
        // Check for any missing parameters
        if(false === $this->_request->hasRequiredParameters())
        {
            $missing = $this->_request->getMissingParameters();
            throw new Exception\ParameterException(
                'Missing required oauth_ parameters:'
                . rtrim(implode(', ', $missing), ', ')
            );
        }

        // Check if the Consumer Credential is missing
        if(null == $this->_consumerCredential)
        {
            throw new \Exception('HMAC_SHA1 requires a Consumer Credential');
        }

        return $this->_generateSignature();
    }

    /**
     * Performs the actual signature generation
     */
    private function _generateSignature()
    {
        // Get the base string
        $baseString = $this->getBaseString();

        $keyString = $this->_consumerCredential->getSecret() . '&';
        if(null !== $this->_accessCredential)
        {
            $keyString .= $this->_accessCredential->getSecret();
        }

        return base64_encode(hash_hmac('sha1', $baseString, $keyString, true));
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