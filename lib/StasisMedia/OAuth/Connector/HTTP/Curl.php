<?php
namespace StasisMedia\OAuth\Connector\HTTP;

use StasisMedia\OAuth\Connector;
use StasisMedia\OAuth\Request;
use StasisMedia\OAuth\Utility;

/**
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
     * Headers to supply to the CURL handle
     * @var array
     */
    private $_curlHeaders = array();

    /**
     * The transmission method to use for oauth_ and protocol parameters
     * @var string
     */
    private $_transmissionMethod = self::TRANSMIT_AUTHORIZATION_HEADER;


    /**
     * Post parameters to include in the request
     * @var array
     */
    private $_postParameters = array();

    /**
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
     * @var array
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
     * at execution time.
     * If you want to set POST data, use setPostData(), which will prevent your
     * post data being overwritten by oauth_ parameters
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

    public function setPostParameters(array $postParameters, $merge=true)
    {
        if($merge === true)
        {
            $this->_postParameters = Utility\Parameter::combineParameters(
                    $postParameters,
                    $this->_postParameters
            );
        } else {
            $this->_postParameters = $postParameters;
        }
    }

    /**
     * Sets the transmission method for transferring the protocol and other
     * oauth_ parameters to the Provider
     *
     * @param string $method 
     */
    public function setTransmissionMethod($method)
    {
        switch($method)
        {
            case self::REQUEST_URI_QUERY:
            case self::FORM_ENCODED_BODY:
            case self::TRANSMIT_AUTHORIZATION_HEADER:
                $this->_transmissionMethod = $method;
                break;
            default:
                throw new Exception('Invalid parameter transmission method');
                break;
        }
    }

    /**
     * Prepare the connector for execution
     * 
     * @param RequestInterface $request
     */
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
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HEADER         => true
        );
        
        // See if this should be a POST
        // TODO: Proper HTTP method processing
        if($this->_request->getRequestMethod() == 'POST')
        {
            $options[\CURLOPT_POST] = true;
        }

        $this->setCurlOptions($options);

        // Add the oauth parameters
        $this->_addOAuthParameters();

        // TODO: Add other parameters

        // Add any headers to the request
        if(count($this->_curlHeaders) > 0)
        {
            $this->setCurlOptions(array(
                \CURLOPT_HTTPHEADER => $this->_curlHeaders
            ));
        }

        // Finally apply all of the options
        curl_setopt_array($this->_curlHandle, $this->_curlOptions);
    }

    /**
     * Add the 'oauth_' parameters, according to the transmission method
     */
    private function _addOAuthParameters()
    {
        switch($this->_transmissionMethod)
        {
            case self::TRANSMIT_AUTHORIZATION_HEADER:
                $this->_addOAuthParametersAuthorizationHeader();
                break;
            case self::FORM_ENCODED_BODY:
                $this->_addOAuthParametersFormEncodedBody();
                break;
            default:
                throw new Exception('Not implemented');
                break;
        }
    }

    /**
     * Add the OAuth parameters as per
     * http://tools.ietf.org/html/rfc5849#section-3.5.1
     */
    private function _addOAuthParametersAuthorizationHeader()
    {
        $parameters = $this->_request->getOAuthParameters();
        
        $headerParts = array();

        foreach($parameters as $key => $value)
        {
            $headerParts[] = rawurlencode($key)
                             . '="' . rawurlencode($value) . '"';
        }

        $header = 'Authorization: OAuth ' . implode(', ', $headerParts);

        $this->_curlHeaders[] = $header;
    }

    private function _addOAuthParametersFormEncodedBody()
    {
        if($this->_request->getRequestMethod() !== 'POST')
        {
            throw new Exception('Request method must be POST to send OAuth
                parameters in the request body');
        }
        
        $oAuthParameters = $this->_request->getOAuthParameters();

        $this->_postParameters = Utility\Parameter::combineParameters(
            $this->_postParameters,
            $oAuthParameters
        );

    }

    /**
     * Execute the curl handle
     */
    public function execute()
    {
        if($this->_prepared === false)
        {
            throw new Exception('prepare() must be called before execute()');
        }

        $response = curl_exec($this->_curlHandle);

        $responseParts = explode("\r\n\r\n", $response, 2);
        $this->_response['header'] = $responseParts[0];
        $this->_response['body'] =  $responseParts[1];
        $this->_response['headers'] = $this->_parseHeader($responseParts[0]);

        $this->_executed = true;
    }

    /**
     * Parses a HTTP header into an assoc array. We use this to prevent
     * dependence on PECL HTTP
     *
     * From http://www.php.net/manual/en/function.http-parse-headers.php#77241
     *
     * @param string $header
     * @return array Associative array of all headers
     */
    function _parseHeader($header)
    {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }

    /**
     * Get the response from a CURL request
     * 
     * @return array The response
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
