<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request\RequestInterface;
/**
 * OAuth 1.0 signature base class
 * http://tools.ietf.org/html/rfc5849#section-3.4
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
abstract class Signature implements SignatureInterface
{
    protected $_request;

    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;
    }

    abstract public function buildSignature();
}