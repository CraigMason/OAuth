<?php
namespace StasisMedia\OAuth\Parameter;

$base = dirname(__FILE__) . '/../../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Request/Parameter/Value.php';

use StasisMedia\OAuth\Exception\ParameterException;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValue()
    {
        $value = new Value('one');
        $this->assertEquals('one', $value->get());
        $this->assertEquals('one', (string) $value);

        $value->set('two');
        $this->assertEquals('two', $value->get());
    }

    public function testSetTypes()
    {
        $value = new Value(1);
        
        try
        {
            $value->set(1);
            $value->set('2');
            $value->set(-10);
        } catch (\Exception $e)
        {
            $this->fail('A valid scalar was rejected');
        }

        // Test that the exception was raised
        try {
            $value->set(array(1));
        }
        catch(\Exception $e)
        {
            return;
        }

        $this->fail('Expected exception was not raised');
    }

    /**
     * @dataProvider percentEncodingProvider
     */
    public function testPercentEncoding($a, $b)
    {        
        $value = new Value('');

        $value->set($a);
        $this->assertEquals($b, $value->getPercentEncoded());
    }

    public function percentEncodingProvider()
    {
        return array(
            array('abcABC123', 'abcABC123'),
            array('-._~', 	'-._~'),
            array('%',	'%25'),
            array('+',	'%2B'),
            array('&=*', '%26%3D%2A'),
            array("\x0A", '%0A'),
            array("\x20", '%20'),
            array("\x7F",	'%7F'),
            array("\xc2\x80",	'%C2%80'),
            array("\xE3\x80\x81", '%E3%80%81'),
            array("\x02\x4B\x62", rawurlencode("\x02\x4B\x62"))
        );

    }

    public function testUtf8Encoding()
    {
        $utf8 = "\x06\x0E";
        $utf16 = iconv('UTF-8', 'UTF-16', $utf8);
        
        $value = new Value($utf16);
        $value->setEncoding('UTF-16');
        $this->assertEquals("\x06\x0E", $value->getUtf8Encoded());

        $value->set(iconv('UTF-8', 'UTF-32BE', "\x06\x00"));
        $value->setEncoding('UTF-32BE');
        $this->assertEquals("\x06\x00", $value->getUtf8Encoded());

    }
}