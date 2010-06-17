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
     * Adds the required and optional parameters for all requests
     */
    public function __construct()
    {
        // Required parameters
        $this->addRequiredOAuthParameters(array(
            'oauth_consumer_key'
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
        // Check if any required parameters are missing
        foreach($this->_requiredOAuthParameters as $requiredParameter)
        {
            if(!in_array($requiredParameter, $this->_parameters))
            {
                return false;
            }
        }

        return true;
    }

}