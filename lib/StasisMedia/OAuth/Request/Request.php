<?php
namespace StasisMedia\OAuth\Request;

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
class Request implements RequestInterface
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
        return array_diff($this->_requiredOAuthParameters, array_keys($this->_parameters));
    }

    /**
     * Adds the parameter and value to this request's parameters
     *
     * @param string $parameter
     * @param string $value
     */
    public function setParameter($parameter, $value)
    {
        $this->setParameters(array($parameter => $value));
    }

    /**
     * Sets an array of parameters
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->_parameters = array_merge($this->_parameters, $parameters);
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
     * @return array
     */
    public function getParameters()
    {
        // 1. The query component
        //$this->_getQueryParameters();

        // 2. The Authorization header
        //$this->_getAuthorizationHeaderParameters();

        // 3. The entity-body
        //$this->_getEntityBodyParameters();
    }

    /**
     * Return only the parameters prefixed with 'oauth'
     *
     * @return array
     */
    public function getOAuthParameters()
    {
        // TODO: Rewrite to utilise getParameters()
        /*
        $oauthParameters = array();
        foreach($this->_parameters as $key => $value)
        {
            // Identical to 0, begins with
            if(strpos($key, 'oauth_') === 0)
            {
                $oauthParameters[$key] = $value;
            }
        }

        return $oauthParameters;
        */
    }

    /**
     * Get the key/value pairs of parameters supplied in the query string
     * of the URL
     */
    private function _getQueryParameters()
    {
        $queryString = $this->_urlComponents['query'];
        return self::parseQueryParameters($queryString);
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
     * @param <type> $body
     * @param <type> $contentType
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
     * @param array $parameters
     */
    public function setPostParameters($parameters)
    {
        $this->setEntityBody(
                self::buildQueryString($parameters),
                'application/x-www-form-urlencoded'
        );
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

    /**
     * Parses the query-string of a URI into an associative array. Duplicate
     * keys will transform the parameter into an array
     *
     * @see Request::buildHttpQuery
     *
     * @param string $parameters
     */
    public static function parseQueryParameters($queryString)
    {
        // If there is nothing to parse, return an empty array
        if( isset($queryString) === false || $queryString === false) return array();

        // Resulting parameters
        $parameters = array();

        // Split the key pairs with an ampersand
        $pairs = explode('&', $queryString);

        foreach($pairs as $pair)
        {
            $split = explode('=', $pair, 2);

            // TODO: Can array keys be utf-8 strings?
            $parameter = rawurldecode($split[0]);
            // Value may be blank
            $value = isset($split[1]) ? rawurldecode($split[1]) : '';

            // If the key exists, it must be appended to the list
            if(array_key_exists($parameter, $parameters))
            {
                if(is_scalar($parameters[$parameter]))
                {
                    $parameters[$parameter] = array($parameters[$parameter]);
                }
                $parameters[$parameter][] = $value;
            }
            // Paramater does not exist. Add it to the list normally
            else
            {
                $parameters[$parameter] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Transforms a key/value array of parameters into a HTTP query string.
     * Duplicate name values will be included.
     *
     * @see self::parseQueryParameters()
     *
     * @param array $parameters
     */
    public static function buildQueryString($parameters)
    {
        // If there is nothing to parse, return an empty array
        if( isset($parameters) === false || $parameters === false) return array();

        $pairs = array();

        // Loop through all keys
        foreach($parameters as $parameter => $value)
        {
            if(is_array($value))
            {
                foreach($value as $duplicate)
                {
                    $pairs[] = $parameter . '=' .rawurlencode($duplicate);
                }
            } else {
                $pairs[] = $parameter . '=' .rawurlencode($value);
            }
        }

        return implode('&', $pairs);
    }

}