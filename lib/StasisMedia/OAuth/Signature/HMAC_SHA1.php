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
    public function buildSignature(){}
}