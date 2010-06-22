<?php
namespace StasisMedia\OAuth\Request;

$base = dirname(__FILE__) . '/../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Parameter/Value.php';
require_once $base . '/Parameter/Parameter.php';
require_once $base . '/Parameter/Collection.php';
require_once $base . '/Request/RequestInterface.php';
require_once $base . '/Request/Request.php';

require_once __DIR__ . '/MockRequest.php';

use StasisMedia\OAuth\Exception\ParameterException;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredOAuthParameters()
    {
        $request = new MockRequest();

        $this->assertEquals(false, $request->hasRequiredParameters());

        $request->setOAuthParameter('oauth_consumer_key', 'dpf43f3p2l4k3l03');
        $request->setOAuthParameter('oauth_signature_method', 'HMAC-SHA1');

        $this->assertEquals(true, $request->hasRequiredParameters());

        $request->setOAuthParameter('oauth_nonexistent_value', 'foo');
        $this->assertEquals(true, $request->hasRequiredParameters());
    }

    public function testGetPostParameters()
    {
        $request = new MockRequest();
        $string = "a=x%20y&a=x%21y";
        $request->setPostParameters($string);

        $collection = $request->getPostParameters();

        $this->assertEquals(1, count($collection->get('a')));
        $this->assertEquals('a=x%20y&a=x%21y', $collection->get('a')->getNormalized());
    }

    public function testGetParameters()
    {
        $request = new MockRequest();
        $request->setUrl('http://example.com/?a=1&b=2');

        $string = "a=3&b=4";
        $request->setPostParameters($string);

        $request->setOAuthParameter('oauth_consumer_key', 'dpf43f3p2l4k3l03');
        $request->setOAuthParameter('oauth_signature_method', 'HMAC-SHA1');

        $collection = $request->getParameters();
        
        $this->assertEquals(4, count($collection->getAll()));
        $this->assertEquals('a=1&a=3', $collection->get('a')->getNormalized());
        
        $normalized = 'a=1&a=3&b=2&b=4&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_signature_method=HMAC-SHA1';
        $this->assertEquals($normalized, $collection->getNormalized());

    }

    public function testBaseStringURI()
    {
        $url = 'http://EXAMPLE.COM:80/r%20v/X?id=123';
        $request = new MockRequest();
        $request->setUrl($url);

        $this->assertEquals('http://example.com/r%20v/X', $request->getBaseStringURI());
    }

}