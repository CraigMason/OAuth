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

    public function testReset()
    {
       $collection = new Collection();
       $collection->add('alpha', 'foo');
       $collection->add('bravo', array('foo', 'bar'));

       // Reset parameter 'bravo' to single value 'test'
       $collection->reset('bravo', 'test');
       $value = reset($collection->get('bravo')->getValues());
       $this->assertEquals('test', (string) $value);

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

    public function testFromQueryString()
    {
        $queryString = 'a=x%20y&a=x%21y';
        $collection = \StasisMedia\OAuth\Parameter\Collection::fromQueryString($queryString);

        $parameters = $collection->getAll();
        $this->assertEquals(1, count($parameters));

        $values = $collection->get('a')->getValues();

        $this->assertEquals('x y', $values[0]->get());
        $this->assertEquals('x!y', $values[1]->get());

        $queryString = 'arabic=%D9%81%D8%B5%D8%AD%D9%89';
        $collection = \StasisMedia\OAuth\Parameter\Collection::fromQueryString($queryString);
        $value = reset($collection->get('arabic')->getValues());

        $this->assertEquals("\xd9\x81\xd8\xb5\xd8\xad\xd9\x89", $value->get());
    }
}