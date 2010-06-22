<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Utility;
use \StasisMedia\OAuth\Parameter;

/**
 * OAuth 1.0 Request
 *
 * Models the components of a HTTP message that are used when sending an OAuth
 * signed request. Whilst this class models a number of elements of a HTTP
 * message, it is only concerned with holding parameters, headers and other
 * entities for the purpose of generating a base string.
 *
 * StasisMedia\OAuth\Client can create a CURL-based HTTP request from this
 * model.
 *
 * The term 'Request' is used, because it is representative of both a 'HTTP
 * Message' and a 'OAuth Request'.
 *
 * Will set all default REQUIRED and OPTIONAL parameters according to
 * http://tools.ietf.org/html/rfc5849#section-3
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
abstract class Request implements RequestInterface
{
    const GET  =   'GET';
    const POST =   'POST';
    const PUT  =   'PUT';
    const DELETE = 'DELETE';

    /*
     * A number of parameters are USUALLY required, but when using PLAINTEXT
     * signature method, many can be excluded.
     *
     * http://tools.ietf.org/html/rfc5849#section-3.1
     */

    /**
     * Required OAuth parameters for the request
     * @var array
     */
    private $_requiredOAuthParameters = array();

    /**
     * Optional OAuth parameters for the request
     * @var array
     */
    private $_optionalOAuthParameters = array();

    /**
     * Array of HTTP headers
     * @var array;
     */
    private $_headers = array();

    /**
     * The entity body of the HTPT message
     * @var string
     */
    private $_entityBody;

    /**
     * The HTTP Request method
     * @var string
     */
    private $_requestMethod = 'GET';

    /**
     * Full URL of this request
     * @var string
     */
    private $_url;

    /**
     * URL components (from parse_url()) of the URL for this request
     * @var array
     */
    private $_urlComponents;


    /**
     * The oauth parameters that do not exist elsewhere in the request
     * @var Parameter\Collection
     */
    private $_oauthParameters;

    /**
     * Adds the required and optional parameters for all requests
     */
    public function __construct()
    {
        // Required parameters
        $this->addRequiredOAuthParameters(array(
            'oauth_consumer_key',
            'oauth_signature_method'
        ));

        // Optional parameters
        $this->addOptionalOAuthParameters(array(
            'oauth_token',
            'oauth_version'
        ));

        $this->_oauthParameters = new Parameter\Collection();
    }

    /**
     * Adds additional REQUIRED parameters to this request
     * @param array $parameters Additional required parameters
     */
    public function addRequiredOAuthParameters(array $parameters)
    {
        foreach($parameters as $parameter)
        {
            if(!in_array($parameter, $this->_requiredOAuthParameters))
            {
                $this->_requiredOAuthParameters[] = $parameter;
            }
        }
    }

    /**
     * Adds additional OPTIONAL parameters to this request
     * @param array $parameters Additional optional parameters
     */
    public function addOptionalOAuthParameters(array $parameters)
    {
        foreach($parameters as $parameter)
        {
            if(!in_array($parameter, $this->_optionalOAuthParameters))
            {
                $this->_optionalOAuthParameters[] = $parameter;
            }
        }
    }

    /**
     * Check if all of the required parameters are present
     * @return bool
     */
    public function hasRequiredParameters()
    {
        $missingParameters = $this->getMissingParameters();
        if(count($missingParameters) > 0) return false;

        return true;
    }

    public function getMissingParameters()
    {
        return array_diff($this->_requiredOAuthParameters, $this->getOAuthParameters()->getNames());
    }

    /**
     * Collects the parameters from a number of collections, according to
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.3.1
     *
     * * The query component of the URI
     * * The OAuth 'Authorization' header field
     * * The entity body, only if:
     *   * It is single part
     *   * It is 'application/x-www-form-urlencoded'
     *   * The 'Content-Type' header is 'application/x-www-form-urlencoded'
     *
     * Should never return an oauth_signature, as we do not allow it to be
     * set within this class.
     *
     * Parameters should all be decoded, according to
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.3
     *
     * @return Parameter\Collection
     */
    public function getParameters()
    {
        return Parameter\Collection::merge(
            // 1. The query component
            $this->_getQueryParameters(),
            // 2. The Authorization header
            $this->_getAuthorizationHeaderParameters(),
            // 3. The entity-body
            $this->_getEntityBodyParameters(),
            // 4. Other OAuth parameters we generate or add
            $this->getOAuthParameters()
        );
    }

    /**
     * Set a single oauth_ parameter
     *
     * @param string $key
     * @param string $value
     */
    public function setOAuthParameter($key, $value)
    {
        if($key === 'oauth_signature') return;
        
        $this->setOAuthParameters(array($key => $value));
    }

    /**
     * Set an array of oauth_ parameters, overwriting old values
     *
     * @param array $parameters
     */
    public function setOAuthParameters(array $parameters)
    {
        foreach($parameters as $key => $value)
        {
            if($key === 'oauth_signature') continue;
            $this->_oauthParameters->reset($key, $value);
        }
    }

    /**
     * Return the oauth parameters
     *
     * @return Parameter\Collection
     */
    public function getOAuthParameters()
    {
        return $this->_oauthParameters;
    }

    /**
     * Set the endpoint of the Request
     *
     * @param String $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        $this->_urlComponents = parse_url($this->_url);
    }

    /**
     * Get the endpoint of the Request
     *
     * @return String
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * The HTTP (or other, custom) request method
     * @param string $method
     */
    public function setRequestMethod($method)
    {
        $this->_requestMethod = $method;
    }


    /**
     * The HTTP (or other, custom) request method
     * @return string $method
     */
    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    /**
     * Set the entity body. Note: the paramaters will not be returned from the
     * entity body if the 'Content-Type' header is set to 'application/x-www-
     * form-urlencoded'.
     *
     * This function will generally be called from $this->setPostData();
     *
     * @see setPostData
     * @see _getParameters
     *
     * @param string $body
     * @param string $contentType
     */
    public function setEntityBody($entityBody, $contentType = null)
    {
        $this->_entityBody = $entityBody;

        if(empty($contentType) === false)
        {
            $this->_headers['Content-Type'] = $contentType;
        }

    }

    /**
     * Set the entity-body to a query string derived from the parameters,
     * and set the 'Content-Type' header to 'application/x-www-form-urlencoded'
     *
     * @param string $queryString
     */
    public function setPostParameters($queryString)
    {
        $this->setEntityBody(
            $queryString,
            'application/x-www-form-urlencoded'
        );
    }

    /**
     * Returns the parameters currently in the entity body as a post request
     *
     * @return Parameter\Collection
     */
    public function getPostParameters()
    {
        return $this->_getEntityBodyParameters();
    }

    /**
     * Set the Authorization header to auth-scheme 'OAuth' and construct the
     * header from the parameters
     *
     * @param array Parameters
     * @param bool $encoded if the supplied parameters are encoded
     */
    public function setOAuthAuthorizationHeader($parameters, $encoded = false)
    {
        $pairs = array();
        foreach($parameters as $key => $value)
        {
            // Check if we need to encoded the parameters
            $key = $encoded ? $key : rawurlencode($key);
            $value = $encoded ? $value : rawurlencode($value);

            $pairs[] = $key . '="' . $value . '"';
        }

        // Set the header
        $this->_headers['Authorization'] = 'OAuth ' . implode(',', $pairs);
    }

    /**
     * Get the key/value pairs of parameters supplied in the query string
     * of the URL
     *
     * @return Parameter\Collection rawurldecoded
     */
    private function _getQueryParameters()
    {
        if(array_key_exists('query', $this->_urlComponents) === false) return null;

        return Parameter\Collection::fromQueryString($this->_urlComponents['query']);
    }

    /**
     * Get the parameters from the Authorization header. We do not provide
     * an interface to set a different scheme, so we immediately parse
     * the parameters.
     *
     * The parameters will already be rawurlencoded
     *
     * @return Array rawurlencoded key/value pairs
     */
    private function _getAuthorizationHeaderParameters()
    {
        if(array_key_exists('Authorization', $this->_headers) === false) return null;

        return Parameter\Collection::fromAuthorizationHeader($this->_headers['Authorization']);
    }

    /**
     * Get the entity-body parameters, only if:
     *   * It is single part
     *   * It is 'application/x-www-form-urlencoded'
     *   * The 'Content-Type' header is 'application/x-www-form-urlencoded'
     *
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.3.1
     *
     * @return Parameter\Collection
     */
    private function _getEntityBodyParameters()
    {
        // If there is no entity body, return an empty array
        if(empty($this->_entityBody) === true) return null;

        // If no 'Content-Type' header
        if(array_key_exists('Content-Type', $this->_headers) === false) return null;

        return Parameter\Collection::fromEntityBody($this->_entityBody, $this->_headers['Content-Type']);
    }

    /**
     * Constructs the base string
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.2
     */
    public function getBaseStringURI()
    {
        $parts = $this->_urlComponents;

        // http://host
        $baseStringURI =
            strtolower($parts['scheme'])
            . '://'
            . strtolower($parts['host']);

        /*
         * http://tools.ietf.org/html/rfc5849#section-3.4.1.2
         *
         * The port MUST be included if it is not the default port for the
         * scheme, and MUST be excluded if it is the default.  Specifically,
         * the port MUST be excluded when making an HTTP request [RFC2616]
         * to port 80 or when making an HTTPS request [RFC2818] to port 443.
         * All other non-default port numbers MUST be included.
         */
        $scheme = $parts['scheme'];
        $port = array_key_exists('port', $parts) ? $parts['port'] : '';

        if( empty($port) === false)
        {
           switch($port)
           {
               case 80:
                   if($scheme != 'http') $baseStringURI .= ':' . $port;
                   break;
               case 443:
                   if($scheme != 'http') $baseStringURI .= ':' . $port;
                   break;
               default:
                   $baseStringURI .= ':' . $port;
                   break;
           }
        }

        // Add the path
        $path = array_key_exists('path', $parts) ? $parts['path'] : '/';
        $baseStringURI .= $path;

        // Ignore the query string and fragment
        return $baseStringURI;
    }

    

}