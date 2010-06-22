<?php
namespace StasisMedia\OAuth\Signature;

$base = dirname(__FILE__) . '/../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Parameter/Value.php';
require_once $base . '/Parameter/Parameter.php';
require_once $base . '/Parameter/Collection.php';
require_once $base . '/Request/RequestInterface.php';
require_once $base . '/Request/Request.php';

require_once $base . '/Credential/Consumer.php';
require_once $base . '/Credential/Access.php';

require_once $base . '/Signature/SignatureInterface.php';
require_once $base . '/Signature/Signature.php';
require_once $base . '/Signature/HMAC_SHA1.php';

use StasisMedia\OAuth\Exception\ParameterException;
use StasisMedia\OAuth\Request;
use StasisMedia\OAuth\Credential;

class HMAC_SHA1Test extends \PHPUnit_Framework_TestCase
{
    public function testSignatureMethod()
    {
        $request = new Request\Request();
        $signature = new HMAC_SHA1($request);

        $this->assertEquals('HMAC-SHA1', $signature->getSignatureMethod());
    }

    public function testGenerateSignature()
    {

        $request = new Request\Request();
        $signature = new HMAC_SHA1($request);

        $consumer = new Credential\Consumer();
        $consumer->setSecret('kd94hf93k423kf44');

        $access = new Credential\Access();
        $access->setSecret('pfkkdhi9sl3r4s00');

        $signature->setConsumerCredential($consumer);
        $signature->setAccessCredential($access);

        $request->setUrl('http://photos.example.net/photos?file=vacation.jpg&size=original');
        $request->setOAuthParameters(array(
            'oauth_consumer_key' => "dpf43f3p2l4k3l03",
            'oauth_token' => "nnch734d00sl2jdk",
            'oauth_signature_method' => "HMAC-SHA1",
            'oauth_timestamp' => "1191242096",
            'oauth_nonce' => "kllo9940pd9333jh",
            'oauth_version' => "1.0"
        ));

        $signature = $signature->generateSignature();

        $this->assertEquals('tR3+Ty81lMeYAr/Fid0kMTYa/WM=', $signature);
    }
}