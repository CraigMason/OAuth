<?php
namespace StasisMedia\OAuth\Request;

$base = dirname(__FILE__) . '/../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Parameter/Value.php';
require_once $base . '/Parameter/Parameter.php';
require_once $base . '/Parameter/Collection.php';
require_once $base . '/Request/RequestInterface.php';
require_once $base . '/Request/Request.php';
require_once $base . '/Request/TemporaryCredentials.php';
require_once $base . '/Response/HTTP.php';

require_once __DIR__ . '/MockRequest.php';

use StasisMedia\OAuth\Exception\ParameterException;

class TemporaryCredentialsTest extends \PHPUnit_Framework_TestCase
{
    public function testParseResponse()
    {
        // Generate the query string
        $collection = new \StasisMedia\OAuth\Parameter\Collection();
        $collection->add('oauth_token', 'token');
        $collection->add('oauth_token_secret', 'secret');
        $collection->add('oauth_callback_confirmed', 'true');
        $queryString = $collection->getNormalized();


        $response = new \StasisMedia\OAuth\Response\HTTP();
        $response->setHeaders(array('Status' => '200 OK'));
        $response->setBody($queryString);

        $request = new TemporaryCredentials();

        $responseCollection = $request->parseResponse($response);

        $this->assertEquals('token', $responseCollection->get('oauth_token')->getFirstValue()->get());
        $this->assertEquals('secret', $responseCollection->get('oauth_token_secret')->getFirstValue()->get());
        $this->assertEquals('true', $responseCollection->get('oauth_callback_confirmed')->getFirstValue()->get());
        
    }
}