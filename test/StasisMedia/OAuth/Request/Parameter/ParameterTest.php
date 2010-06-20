<?php
namespace StasisMedia\OAuth\Parameter;

$base = dirname(__FILE__) . '/../../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Request/Parameter/Value.php';
require_once $base . '/Request/Parameter/Parameter.php';

use StasisMedia\OAuth\Exception\ParameterException;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
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
    }
}