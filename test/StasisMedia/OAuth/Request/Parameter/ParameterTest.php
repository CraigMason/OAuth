<?php
namespace StasisMedia\OAuth\Parameter;

$base = dirname(__FILE__) . '/../../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Request/Parameter/Value.php';
require_once $base . '/Request/Parameter/Parameter.php';

use StasisMedia\OAuth\Exception\ParameterException;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $parameter = new Parameter('name', '');
        $this->assertEquals('name', (string) $parameter->getName());
    }

    public function testToString()
    {
        $parameter = new Parameter('name', 'foo');
        $parameter->addValue('bar');
        $this->assertEquals('name=bar&name=foo', (string) $parameter);
    }

    public function testAddValue()
    {
        $parameter = new Parameter('key', 'foo');
        $this->assertEquals(1, count($parameter->getValues()));

        $parameter->addValue('bar2');
        $this->assertEquals(2, count($parameter->getValues()));

        $parameter->addValues(array('foo', 'bar'));
        $this->assertEquals(4, count($parameter->getValues()));

        $parameter->addValue(array('foo', 'bar'));
        $this->assertEquals(6, count($parameter->getValues()));
    }

    public function testReset()
    {
        $parameter = new Parameter('key', 'alpha');
        $parameter->addValue('bravo');
        $parameter->addValue('charlie');
        $parameter->reset('delta');

        $this->assertEquals(1, count($parameter->getValues()));
        
        $value = reset($parameter->getValues());
        $this->assertEquals('delta', (string) $value);

    }

    public function testGetValue()
    {
        $parameter = new Parameter('key', 'foo');
        $values = $parameter->getValues();
        $this->assertEquals('foo', (string) $values[0]);

        $parameter->addValue('bar');
        $values = $parameter->getValues();
        $this->assertEquals('foo', (string) $values[0]);
        $this->assertEquals('bar', (string) $values[1]);

        $value = $values[1];
        $this->assertEquals('bar', $value->get());
    }

    public function testNormalized()
    {
        $parameter = new Parameter('name', '');
        $this->assertEquals('name=', $parameter->getNormalized());

        $parameter = new Parameter('a', 'b');
        $this->assertEquals('a=b', $parameter->getNormalized());

        $parameter->addValue('a');
        $this->assertEquals('a=a&a=b', $parameter->getNormalized());

        $parameter = new Parameter('x!y', array('b', 'a'));
        $this->assertEquals('x%21y=a&x%21y=b', $parameter->getNormalized());
        
        $parameter = new Parameter("x\xD8\x80y", array("\x03\x04", "\x01\x02"));
        $this->assertEquals('x%D8%80y=%01%02&x%D8%80y=%03%04', $parameter->getNormalized());
    }
}