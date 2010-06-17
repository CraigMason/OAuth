<?php
namespace StasisMedia\OAuth\Request;

/*
 * OAuth 1.0 request
 *
 * Base class will all default REQUIRED and OPTIONAL parameters
 * http://tools.ietf.org/html/rfc5849#section-3
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
class Request implements RequestInterface
{
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
     * @var <type>
     */
    private $_optionalOAuthParameters = array();

    /**
     * Key/Value pairs of parameters
     * @var array
     */
    private $_parameters = array();

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

    public function getParameters()
    {
        return $this->_parameters;
    }

    public function setUrl($url)
    {
        $this->_url = $url;

        $this->_urlComponents = parse_url($this->_url);
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