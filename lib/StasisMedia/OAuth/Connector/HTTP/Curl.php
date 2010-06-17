<?php
namespace StasisMedia\OAuth\Connector\HTTP;

use StasisMedia\OAuth\Connector;
use StasisMedia\OAuth\Request;
/*
 * OAuth 1.0 HTTP Curl connector
 *
 * CURL connector for communicating with a Service Provider using HTTP
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
class Curl implements Connector\ConnectorInterface
{
    const TRANSMIT_AUTHORIZATION_HEADER = 'authorization_header';
    const FORM_ENCODED_BODY             = 'form_encoded_body';
    const REQUEST_URI_QUERY             = 'request_uri_query';

    /**
     * The CURL handle
     * @var Resource
     */
    private $_curlHandle;

    /**
     * CURL handle options
     * @var array
     */
    private $_curlOptions = array();

    /**
     * The transmission method to use for oauth_ and protocol parameters
     * @var <type>
     */
    private $_transmissionMethod = self::TRANSMIT_AUTHORIZATION_HEADER;

     * Request
     * @var Request\RequestInterface;
     */
    private $_request;

    /**
     * Prepared for execution?
     * @var bool
     */
    private $_prepared = false;

    /**
     * Has the request been executed?
     * @var bool
     */
    private $_executed = false;

    /**
     * The response from the Provider;
     * @var <type>
     */
    private $_response = array();

    /**
     * Set up the CURL handle
     */
    public function __construct()
    {
        $this->_curlHandle = curl_init();
    }

    /**
     * Set CURL options for the CURL handle. NOTE: These may get overridden
     * at execution time
     * 
     * @param array $options
     */
    public function setCurlOptions(array $options)
    {
        foreach($options as $key => $value)
        {
            $this->_curlOptions[$key] = $value;
        }
    }

    public function prepare(Request\RequestInterface $request)
    {
        $this->_request = $request;

        $this->_setupCurlRequest();

        $this->_prepared = true;
    }

    /**
     * Set up the CURL handle with properties from the Request
     */
    private function _setupCurlRequest()
    {
        // Set some mandatory options
        $options = array(
            \CURLOPT_URL            => $this->_request->getUrl(),
            \CURLOPT_RETURNTRANSFER => true
        );

        $this->setCurlOptions($options);

        curl_setopt_array($this->_curlHandle, $options);
    }

    public function execute()
    {
        if($this->_prepared === false)
        {
            throw new Exception('prepare() must be called before execute()');
        }

        $this->_response['body'] = curl_exec($this->_curlHandle);
        $this->_response['headers'] = curl_getinfo($this->_curlHandle);

        $this->_executed = true;
    }

    public function getResponse()
    {
        return $this->_response;
    }
}
