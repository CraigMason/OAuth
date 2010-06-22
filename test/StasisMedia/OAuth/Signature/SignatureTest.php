<?php
namespace StasisMedia\OAuth\Signature;

$base = dirname(__FILE__) . '/../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Parameter/Value.php';
require_once $base . '/Parameter/Parameter.php';
require_once $base . '/Parameter/Collection.php';
require_once $base . '/Request/RequestInterface.php';
require_once $base . '/Request/Request.php';

require_once $base . '/Signature/SignatureInterface.php';
require_once $base . '/Signature/Signature.php';

use StasisMedia\OAuth\Exception\ParameterException;
use StasisMedia\OAuth\Request;

class SignatureTest extends \PHPUnit_Framework_TestCase
{

    public function testBaseString()
    {
        //die(rawurldecode('POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk9d7dh3k39sjv7'));


        $request = new Request\Request();
        $signature = new MockSignature($request);

        $request->setUrl('http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b');
        $request->setOAuthParameters(array(
            'oauth_consumer_key' => "9djdj82h48djs9d2",
            'oauth_token' => "kkk9d7dh3k39sjv7",
            'oauth_signature_method' => "HMAC-SHA1",
            'oauth_timestamp' => "137131201",
            'oauth_nonce' => "7d8f3e4a",
            'oauth_signature' => "bYT5CMsGcbgUdFHObYMEfcx6bsw%3D"
        ));
        $request->setRequestMethod(Request\Request::POST);
        $request->setPostParameters('c2&a3=2+q');

        $baseString = 'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q'
        . '%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_'
        . 'key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_m'
        . 'ethod%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk'
        . '9d7dh3k39sjv7';

        $this->assertEquals($baseString, $signature->getBaseString());
    }
}

class MockSignature extends Signature
{
    public function getSignatureMethod(){return 'Mock';}
    public function generateSignature(){}
}