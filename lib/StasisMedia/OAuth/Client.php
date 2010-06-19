<?php
namespace StasisMedia\OAuth;

use StasisMedia\OAuth\Connector;

/**
 * OAuth 1.0 client - rfc5849
 * http://tools.ietf.org/html/rfc5849
 *
 * Uses the terminology as specified in rfc5849
 *
 * This library will not initiate any redirection, echo output or otherwise
 * interfere with the parent application. Redirection is the responsibility
 * of the application.
 *
 * urlencoding should be rfc3986 compliant. PHP 5.3 rawurlencode() is rfc3986
 * compliant. Prior to 5.3, it was rfc1738 compliant.
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
class Client
{
    /**
     * Connector for making requests to the Service Provider
     * @var Connector\ConnectorInterface
     */
    private $_connector;

    /**
     * Request type
     * @var Request\RequestInterface
     */
    private $_request;

    /**
     * Signature algorithm
     * @var Signature\SignatureInterface
     */
    private $_signature;

    /**
     * A unix timestamp
     * @var int
     */
    private $_timestamp;

    /**
     * Are the Request and Connector prepared?
     * @var bool
     */
    private $_prepared = false;

    /**
     * Has the request been executed?
     * @var bool
     */
    private $_executed = false;

    /**
     *
     * @param   ConnectorInterface $connector The HTTP connector to use when
     *          communicating with the Service Provider
     */
    public function __construct(Connector\ConnectorInterface $connector,
                                Request\RequestInterface $request,
                                Signature\SignatureInterface $signature)
    {
        $this->_connector = $connector;
        $this->_request = $request;
        $this->_signature = $signature;

        $this->_request->setOAuthParameter('oauth_version', '1.0');
    }

    /**
     * Generates a 64-bit one-time nonce
     *
     * @return string The unique nonce
     */
    private function _generateNonce()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Returns the current unix timestamp
     * @return int Unix timestamp
     */
    private function _generateTimestamp()
    {
        return time();
    }

    /**
     * We allow timestamps to be set, as the OAuth spec does not excplicitly
     * specify how timestamps should synhronize between Consumer and Provider.
     * Perhaps you want to use a UTC time, or perhaps your Provider needs a
     * timestamp within a specified timeframe.
     *
     * If a timestamp is set with this method, it will be used as a parameter
     * in the Request
     */
    public function setTimestamp($timestamp)
    {
        $this->_timestamp = $timestamp;
    }

    /**
     * Prepares the connector for execution. We provide 'prepare' as a seperate
     * method, as users may with to utilise curl_multi or other connectors
     */
    public function prepare()
    {
        // Prepare the request
        $this->_request->setOAuthParameters(array(
            'oauth_nonce' => $this->_generateNonce(),
            'oauth_timestamp' => $this->_generateTimestamp()
        ));

        // Generate the signature
        $signature = $this->_signature->generateSignature();

        // Add the signature to the request
        $this->_request->setOAuthParameter('oauth_signature', $signature);

        // Add any post parameters from the Request to the Connector
        $this->_connector->setPostParameters($this->_request->getPostParameters());

        // Call the prepare method on the request
        $this->_connector->prepare($this->_request);

        $this->_prepared = true;
    }

    /**
     * Execute the request. Will call prepare() if not already done so.
     */
    public function execute()
    {
        // If we have not yet prepared, do so
        if($this->_prepared === false) $this->prepare();

        // Execute
        $this->_connector->execute();

        $this->_executed = true;
    }

    /**
     * Return the full response array from the connector
     *
     * @return Array The response array (header, headers, body);
     */
    public function getResponse()
    {
        return $this->_connector->getResponse();
    }
}
