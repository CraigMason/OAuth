<?php
Namespace StasisMedia\OAuth\Signature;

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
    public function __construct(RequestInterface $request)
    {
        parent::__construct($request);

        // Set the other required parameters
        $request->addRequiredOAuthParameters(array(
            'oauth_timestamp',
            'oauth_nonce',
            'oauth_timestamp',
        ));
    }

    public function buildSignature(){}
}