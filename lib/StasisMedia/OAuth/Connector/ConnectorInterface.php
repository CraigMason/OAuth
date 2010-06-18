<?php
namespace StasisMedia\OAuth\Connector;

use StasisMedia\OAuth\Request;

/**
 * OAuth 1.0 connector interface
 *
 * Provides a mechanism to communicate with a Service Provider.
 * OAuth 1.0 specifies a HTTP interface. We do not enforce HTTP transport
 * at the interface level
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 * @subpackage  Connector
 */
interface ConnectorInterface
{
    public function setTransmissionMethod($method);

    public function prepare(Request\RequestInterface $request);
    public function execute();
    public function getResponse();    
}
