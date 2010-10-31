<?php
namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request;
use StasisMedia\OAuth\Credential;

class HMAC_SHA1 implements SignatureInterface
{
    const SIGNATURE_METHOD = 'HMAC-SHA1';
    /**
     *
     * @var RequestInterface
     */
    private $request;

    /**
     *
     * @var Consumer
     */
    private $consumer;

    /**
     * @var Credential\Access
     */
    private $accessCredential;

    public function __construct(Request\RequestInterface $request,
                                Credential\Consumer $consumer,
                                Credential\Access $accessCredential = null)

    {
        $this->request = $request;
        $this->consumer = $consumer;
        $this->accessCredential = $accessCredential;
    }

    /**
     * Generate the oauth_signature parameter
     * @return string
     */
    public function generateSignature()
    {
        $keyString = rawurlencode($this->consumer->getSecret()) . '&';

        if($this->accessCredential !== null)
        {
            $keyString .= \rawurlencode($this->accessCredential->getSecret());
        }

        $baseString = $this->request->getBaseString(self::SIGNATURE_METHOD);

        return \base64_encode(hash_hmac('sha1', $baseString, $keyString, true));
    }
}