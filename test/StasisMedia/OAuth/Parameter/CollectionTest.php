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
        // Check we're using 'application/x-www-form-urlencoded decding
        $queryString = 'a=1+2';
        $collection = \StasisMedia\OAuth\Parameter\Collection::fromQueryString($queryString);
        $parameter = $collection->get('a');
        $value = reset($parameter->getValues());
        $this->assertEquals('1 2', (string) $value);


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

    public function testFromAuthorizationHeader()
    {
        $header = <<<EOT
OAuth realm="Example",
oauth_consumer_key="0685bd9184jfhq22",
oauth_token="ad180jjd733klru7",
oauth_signature_method="HMAC-SHA1",
oauth_signature="wOJIO9A2W5mFwDgiDvZbTSMK%2FPY%3D",
oauth_timestamp="137131200",
oauth_nonce="4572616e48616d6d65724c61686176",
oauth_version="1.0"
EOT;
        $collection = Collection::fromAuthorizationHeader($header);
        $parameters = $collection->getAll();
        $this->assertEquals(7, count($parameters));
        $this->assertEquals(true, $collection->exists('oauth_signature'));
        
        $value = reset($collection->get('oauth_token')->getValues());
        $this->assertEquals('ad180jjd733klru7', (string) $value);

        $value = reset($collection->get('oauth_version')->getValues());
        $this->assertEquals('1.0', (string) $value);

        $value = reset($collection->get('oauth_signature')->getValues());
        $this->assertEquals('wOJIO9A2W5mFwDgiDvZbTSMK%2FPY%3D', (string) $value);

    }

    public function testFromEntityBody()
    {
        $entityBody = 'Name=Jonathan+Doe&Age=23&Formula=a+%2B+b+%3D%3D+13%25%21';
        $collection = Collection::fromEntityBody($entityBody, 'application/x-www-form-urlencoded');

        $parameter = $collection->get('Formula');
        $this->assertTrue(isset($parameter));

        $value = reset($parameter->getValues());
        $this->assertEquals('a + b == 13%!', (string) $value);
    }

    public function testMerge()
    {
        $collection1 = new Collection();
        $collection1->add('alpha', 'test1');
        $collection1->add('bravo', 'test2');

        $collection2 = new Collection();
        $collection2->add('alpha', 'test3');
        $collection2->add('bravo', 'test4');
        $collection2->add('bravo', 'test5');

        $collection3 = Collection::merge($collection1, $collection2);

        $parameters = $collection3->getAll();

        $this->assertEquals(2, count($parameters));

        $this->assertEquals(2, count($collection3->get('alpha')->getValues()));
        $this->assertEquals(3, count($collection3->get('bravo')->getValues()));

        $normalized = 'bravo=test2&bravo=test4&bravo=test5';
        $this->assertEquals($normalized, $collection3->get('bravo')->getNormalized());

    }

    /**
     * @expectedException Exception
     */
    public function testMergeException()
    {
        Collection::merge(array(null));
    }
}