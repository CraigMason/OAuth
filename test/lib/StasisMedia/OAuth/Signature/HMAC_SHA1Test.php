<?php

namespace StasisMedia\OAuth\Signature;

require_once __DIR__ . '/../../../../bootstrap.php';

use StasisMedia\OAuth\Parameter\Parameter;

class HMAC_SHA1Test extends \PHPUnit_Framework_TestCase {

    public function testGenerateSignature()
    {
        $consumer = new \StasisMedia\OAuth\Credential\Consumer('9djdj82h48djs9d2', 'j49sk3j29djd');
        
        $request = new \StasisMedia\OAuth\Request\MockRequest($consumer, 'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b', 'POST', false);
        $request->setPostParameters(Parameter::fromQueryString('c2&a3=2+q'));
        $request->setAuthorizationHeader('Authorization: OAuth realm="Example",
            oauth_consumer_key="9djdj82h48djs9d2",
            oauth_token="kkk9d7dh3k39sjv7",
            oauth_signature_method="HMAC-SHA1",
            oauth_timestamp="137131201",
            oauth_nonce="7d8f3e4a"');

        /*
        $test =  'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q'
            . '%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_'
            . 'key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_m'
            . 'ethod%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk'
            . '9d7dh3k39sjv7';
         */

        $access = new \StasisMedia\OAuth\Credential\Access('kkk9d7dh3k39sjv7', 'dh893hdasih9');

        $signature = new HMAC_SHA1($request, $consumer, $access);

        // Errata 2550
        //$this->assertEquals('bYT5CMsGcbgUdFHObYMEfcx6bsw%3D', rawurlencode($signature->generateSignature()));
        $this->assertEquals('r6%2FTJjbCOr97%2F%2BUU0NsvSne7s5g%3D', rawurlencode($signature->generateSignature()));

    }
    

}