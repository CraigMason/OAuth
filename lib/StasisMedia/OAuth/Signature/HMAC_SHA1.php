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
     * Normalizes the the parameters
     * @param <type> $parameters
     */
    private function _normalizeParameters($parameters)
    {
        $parameters = $this->_encodeParameters($parameters);
    }

    /**
     * Encodes the key-value pairs according to
     * http://tools.ietf.org/html/rfc5849#section-3.6
     * 
     * @param array $parameters
     */
    private function _encodeParameters($parameters)
    {
        array_walk($parameters, array($this, '_encodeKeyValue'));

        return $parameters;
    }

    /**
     * Encodes a key/value pair, utf8
     *
     * @param string $value
     * @param string $key
     */
    private function _encodeKeyValue(&$value, &$key)
    {
        // Only encode strings
        $key = is_string($key) ? $this->_utf8Encode($key) : $key;
        $value = is_string($value) ? $this->_utf8Encode($value) : $value;
    }

    /**
     * Detects whether a string is UTF-8. If not, we will attempt to
     *
     * @param <type> $string
     * @return <type>
     */
    private function _utf8Encode($string)
    {
        // auto detects from ASCII,JIS,UTF-8,EUC-JP,SJIS
        $encoding = mb_detect_encoding($string);

        // If we cannot detect the encoding, throw an exception
        if($encoding === false)
        {
            throw new Exception\ParameterException(sprintf(
                    'Encoding of stringcould not be detected: ',
                    $string
            ));
        }

        /*
         * If we can detect the encoding, and it is not UTF-8, convert it.
         * The application should supply pre encoded parameters, but the
         * specification appears to indicate that they should be encoded at
         * encoding time.
         * ASCII,JIS,UTF-8,EUC-JP,SJIS
         */
        if($encoding != 'UTF-8')
        {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return $string;
    }

    /**
     * Sort
     * @param <type> $parameters
     */
    private function _sortParameters($parameters)
    {

    }
}