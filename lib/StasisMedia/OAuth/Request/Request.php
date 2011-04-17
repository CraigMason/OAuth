<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Parameter;
use StasisMedia\OAuth\Credential;

abstract class Request implements RequestInterface
{
    const POST_FORM = 'application/x-www-form-urlencoded';
    const POST_MULTIPART = 'multipart/form-data';

    protected $url;
    protected $verb;

    /**
     * Strictly we are looking for an 'entity body' combined with the rules in
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.3.1, but this
     * just equates to post parameters
     *
     * @var Array of StasisMedia\OAuth\Parameter\Parameter
     */
    protected $postParameters = array();

    /**
     * Array of StasisMedia\OAuth\Parameter\Parameter
     * @var array
     */
    protected $oauthParameters = array();

    /**
     *
     * @param string $url URL of the request, including query string
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE etc)
     */
    public function __construct(Credential\Consumer $consumer, $url,
                                $verb = 'GET', $includeVersion = true)
    {
        $this->setOAuthParameter(
            new Parameter\Parameter('oauth_consumer_key', $consumer->getKey())
        );

        $this->generateTimestamp();
        $this->generateNonce();

        $this->url = $url;
        $this->verb = \strtoupper($verb);
        if($includeVersion)
        {
            $this->setOAuthParameter(new Parameter\Parameter('oauth_version', '1.0'));
        }        
    }

    /**
     * Regenerate the per-request items. This allows the client to re-use the
     * same Request object for many connections to the same endpoint
     */
    public function reset()
    {
        $this->generateTimestamp();
        $this->generateNonce();
    }

    /**
     * Get the fully normalized parameters from all sources
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.3.2
     *      *
     * @return string Normalized string
     */
    public function getNormalizedParameters()
    {
        // Query string parameters come back in an associative array, decoded
        $parameters = $this->getQueryStringParameters();
        $parameters = is_array($parameters) ? $parameters : array();
        
        // Check if any of the queryParameters are dupes of postParameters
        \array_map(function($parameter) use (&$parameters){
            $name = $parameter->getName();

            // If this is a dupe
            if(\array_key_exists($name, $parameters))
            {
                foreach($parameter->getValue() as $value)
                {
                $parameters[$name]->addValue($value)    ;
                }
            } else {
                // Not a dupe. Add it
                $parameters[$name] = $parameter;
            }
        }, $this->getPostParameters());

        // Add the OAuth parameters
        $parameters = \array_merge($parameters, $this->getOAuthParameters());

        // Sort them
        usort($parameters, function($a, $b){
            return strcmp(
                \rawurlencode($a->getName()),
                \rawurlencode($b->getName())
            );
        });

        $pairs = array();
        foreach($parameters as $parameter)
        {
            $pairs[] = $parameter->getNormalized();
        }

        return implode('&', $pairs);
    }

    /**
     * @param array $parameters
     */
    public function setOAuthParameters(array $parameters)
    {
        $this->setParameters($this->oauthParameters, $parameters);
    }

    /**
     * @param Parameter\Parameter $parameter
     */
    protected function setOAuthParameter(Parameter\Parameter $parameter)
    {
        $this->oauthParameters[$parameter->getName()] = $parameter;
    }

    /**
     * Generates a timestamp and nonce, then returns all oauth_* parameters
     * @return Array of StasisMedia\OAuth\Parameter\Parameter
     */
    protected function getOAuthParameters()
    {
        return $this->oauthParameters;
    }

    /**
     * Set the entity-body to a query string derived from the parameters, ONLY
     * if the type is 'application/x-www-form-urlencoded'
     *
     * @param array of Parameter\Parameter
     */
    public function setPostParameters(array $parameters, $type=self::POST_FORM)
    {
        if($type === self::POST_FORM)
        {
            $this->setParameters($this->postParameters, $parameters);
        }
    }

    protected function getPostParameters()
    {
        return $this->postParameters;
    }

    /**
     * Parse the OAuth Authorization header into oauth_ parameters
     * @param string $header
     */
    public function setAuthorizationHeader($header)
    {
        // Remove 'Authorization: OAuth;
        $header = \preg_replace('/^Authorization:\s?OAuth\s?/', '', $header);

        $parameters = array();

        $parts = preg_split('/,\s*/', $header);
        
        foreach($parts as $part)
        {
            $pair = explode('=', $part, 2);

            // Do NOT include the 'realm' parameter
            if($pair[0] === 'realm') continue;

            $parameters[] = new Parameter\Parameter(
                $pair[0],
                \rawurldecode(trim($pair[1], '"'))
            );
        }

        $this->setParameters($this->oauthParameters, $parameters);
    }

    public function getAuthorizationHeader($realm, $signature = null)
    {
        $parts = array();
        foreach($this->getOAuthParameters() as $parameter)
        {
            // OAuth parameters can only have 1 value
            $name = $parameter->getName();
            $values = $parameter->getValue();
            $parts[] = \rawurlencode($name) . '="' . \rawurlencode($values[0]) . '"';
        }
        $parts[] = 'oauth_signature="' . \rawurlencode($signature) . '"';

        return 'Authorization: OAuth realm="' . $realm . '", ' . implode(', ', $parts);
    }

    /**
     * Helper method to validate input as array of Parameter, and set target
     *
     * @param string $target var to set
     * @param array $parameters
     */
    private function setParameters(&$target, $parameters)
    {
        // Validate
        \array_walk($parameters, function($parameter){
            if( !($parameter instanceof Parameter\Parameter))
            {
                throw new \Exception('Only instances of StasisMedia\OAuth\Parameter\Parameter
                                    permitted. Use ::fromArray() to convert.');
            }
        });

        // Populate the key value to help with searching
        array_walk($parameters, function($value, $key) use (&$parameters)
        {
            // If the key is different to the name
            if($key !== $value->getName())
            {
                $parameters[$value->getName()] = $value;
                unset($parameters[$key]);
            }
        });

        $target = $parameters;
    }

    /**
     * Parse the Parameters from the Query String
     * @return array of Parameter\Parameter
     */
    protected function getQueryStringParameters()
    {
        $parts = parse_url($this->url);

        if(\array_key_exists('query', $parts))
        {
            return Parameter\Parameter::fromQueryString($parts['query']);
        }

        return null;
    }


    /**
     * Generate the Base String for the request
     * http://tools.ietf.org/html/rfc5849#section-3.4.1
     *
     * @return string
     */
    public function getBaseString($signatureMethod)
    {
        // Set the _oauth_signature_method
        $this->setOAuthParameter(new Parameter\Parameter('oauth_signature_method',
                                $signatureMethod));

        // 1. The HTTP request method in uppercase
        $baseString = $this->verb . '&';

        // 2. The base string URI after being encoded
        $baseString .= \rawurlencode($this->getBaseStringURI()) . '&';

        // 3. The request parameters as normalized, after being encoded
        $baseString .= \rawurlencode($this->getNormalizedParameters());

        return $baseString;
    }

    /**
     * Generate the Base String URI
     * http://tools.ietf.org/html/rfc5849#section-3.4.1.2
     *
     * @return string
     */
    protected function getBaseStringURI()
    {
        $parts =  parse_url($this->url);

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
                   if($scheme != 'https') $baseStringURI .= ':' . $port;
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
     * Generates a 64-bit one-time nonce
     *
     * @return string The unique nonce
     */
    protected function generateNonce()
    {
        $this->setOAuthParameter(new Parameter\Parameter(
            'oauth_nonce',
            md5(uniqid(rand(), true))
        ));
    }

    /**
     * Returns the current unix timestamp
     * @return int Unix timestamp
     */
    protected function generateTimestamp()
    {
        $this->setOAuthParameter(new Parameter\Parameter(
            'oauth_timestamp',
            time()
        ));
    }

}