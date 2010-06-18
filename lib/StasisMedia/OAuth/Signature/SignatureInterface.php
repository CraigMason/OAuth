<?php
namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request\RequestInterface;

/**
 * OAuth 1.0 Signature Method Interface
 * http://tools.ietf.org/html/rfc5849#section-3.4
 *
 *   OAuth provides three methods for the client to prove its rightful
 *   ownership of the credentials: "HMAC-SHA1", "RSA-SHA1", and
 *   "PLAINTEXT".
 *
 *   OAuth does not mandate a particular signature method... Servers are
 *   free to implement and document their own custom methods.
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
Interface SignatureInterface
{
    public function __construct(RequestInterface $request);

    public function getSignatureMethod();
    public function generateSignature();
}