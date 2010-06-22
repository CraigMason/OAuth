<?php
namespace StasisMedia\OAuth\Request;

/**
 * OAuth 1.0 Request interface
 *
 * A single request type to a Service Provider. One of:
 * * Temporary Credential Request
 * * Resource Owner Authorization
 * * Token Request
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Request
 */
interface RequestInterface
{
    public function addRequiredOAuthParameters(array $parameters);
    public function addOptionalOAuthParameters(array $parameters);

    public function setRequestMethod($method);
    public function getRequestMethod();
    public function setUrl($url);
    public function getUrl();

    public function setOAuthParameter($parameter, $value);
    public function setOAuthParameters(array $parameters);

    /**
     * @return \StasisMedia\OAuth\Parameter\Collection
     */
    public function getOAuthParameters();

    public function setPostParameters($querystring);
    public function getPostParameters();

    /**
     * @return \StasisMedia\OAuth\Parameter\Collection
     */
    public function getParameters();

    public function hasRequiredParameters();
    public function getMissingParameters();
    
    public function getBaseStringURI();
    
}
