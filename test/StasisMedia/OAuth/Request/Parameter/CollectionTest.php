<?php
namespace StasisMedia\OAuth\Parameter;

$base = dirname(__FILE__) . '/../../../../../lib/StasisMedia/OAuth';

require_once $base . '/Exception/ParameterException.php';
require_once $base . '/Request/Parameter/Value.php';
require_once $base . '/Request/Parameter/Parameter.php';
require_once $base . '/Request/Parameter/Collection.php';

use StasisMedia\OAuth\Exception\ParameterException;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAddParameters()
    {
        $collection = new Collection();
        $collection->add('test', array('foo', 'bar'));

        $this->assertEquals(true, $collection->exists('test'));

        $collection->add('bar', 'foo');
        $this->assertEquals(true, $collection->exists('test'));
        $this->assertEquals(true, $collection->exists('bar'));
    }

    public function testGetParameters()
    {
       $collection = new Collection();
       $collection->add('bar', 'foo');
       $collection->add('test', array('foo', 'bar'));

       $this->assertEquals('bar', (string) $collection->get('bar')->getName());
       $this->assertEquals('test', (string) $collection->get('test')->getName());

    }

    public function testAddExistingMergesValues()
    {
       $collection = new Collection();
       $collection->add('foo', 'a');
       $collection->add('foo', 'b');

       $this->assertEquals(2, count($collection->get('foo')->getValues()));
       
       $values = $collection->get('foo')->getValues();
       $this->assertEquals('b', (string) $values[1]);


       $collection->add('foo', array('c', 'd'));
       $this->assertEquals(4, count($collection->get('foo')->getValues()));

       $values = $collection->get('foo')->getValues();
       $this->assertEquals('c', (string) $values[2]);
       $this->assertEquals('d', (string) $values[3]);
    }

    public function testGetNames()
    {
        $collection = new Collection();
        $collection->add('foo', 'a');
        $collection->add('foo', 'b');
        $collection->add('bar', 'a');

        $this->assertEquals(array('bar', 'foo'), $collection->getNames());

        $collection->add('alpha', 'a');
        
        $this->assertEquals(array('alpha', 'bar', 'foo'), $collection->getNames());

    }

    public function testNormalization()
    {
        $collection = new Collection();
        $collection->add('name', '');
        $this->assertEquals('name=', $collection->getNormalized());

        $collection = new Collection();
        $collection->add('a', 'b');
        $this->assertEquals('a=b', $collection->getNormalized());

        $collection = new Collection();
        $collection->add('a', 'b');
        $collection->add('c', 'd');
        $this->assertEquals('a=b&c=d', $collection->getNormalized());

        $collection = new Collection();
        $collection->add('a', 'x!y');
        $collection->add('a', 'x y');
        $this->assertEquals('a=x%20y&a=x%21y', $collection->getNormalized());

        $collection = new Collection();
        $collection->add('x!y', 'a');
        $collection->add('x', 'a');
        $this->assertEquals('x=a&x%21y=a', $collection->getNormalized());
    }
}