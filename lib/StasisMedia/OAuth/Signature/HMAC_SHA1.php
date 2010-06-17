<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Exception;
use StasisMedia\OAuth\Request;
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

    public function buildSignature()
    {
        if(false === $this->_request->hasRequiredParameters())
        {
            throw new Exception\ParameterException('Some required oauth_'
                . ' parameters are missing from the request.');
        }

        // Build the string

        // $keyString = somsumersecret&tokensecret
        // $signature = hash_hmac('sha1', $base_string, $keyString, true)
    }
}