<?php
/*
 * This file is part of the Obernard package.
 *
 * (c) Olivier Bernard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Obernard\PropertyIndexer\Tests;

/**
 * @author olivier Bernard
 */


use  Obernard\PropertyIndexer\PropertyIndexer;
use PHPUnit\Framework\TestCase;
use Obernard\PropertyIndexer\Exception\InvalidObjectException;
use Obernard\PropertyIndexer\Exception\UndefinedKeyException;
// use Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException;


class ObjectIndexerTest extends TestCase
{

    use TestsTrait;


    public function testFillIndexer()
    {
        // create an obj indexer obj.id => obj.value
        $dico = new PropertyIndexer('id', 'value');
        $this->assertEquals(0, count($dico));

        $myObject = self::simpleObject(1, 'Nice Value');

        $dico->add($myObject);

        $this->assertEquals(count($dico), 1);
        $this->assertEquals('Nice Value', $dico->get(1));

        $dico->add($myObject);
        $this->assertEquals(count($dico), 1);
        $this->assertEquals('Nice Value', $dico->get(1));

        $myObject2 = self::simpleObject('azerty', $myObject);


        $dico->add($myObject2);

        $this->assertEquals(2, count($dico));
        $this->assertEquals($myObject, $dico->get('azerty'));
    }



    public function testIsValidObject()
    {

        $dico = new PropertyIndexer('id', 'value');

        $myObject = self::simpleObject(1, 'Nice Value');
        $this->assertTrue($dico->isValid($myObject));
    }

    public function testIsInvalidObject()
    {

        $dico = new PropertyIndexer('identity', 'content');

        $myObject = self::simpleObject(1, 'Nice Value');
        $this->assertFalse($dico->isValid($myObject));
    }

    public function testInvalidObjectException()
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage('Property id is not owned by the object !');

        $myObject = new \stdClass;
        $myObject->invalid = 1;

        $dico = new PropertyIndexer('id', 'value');

        $dico->add($myObject);
    }

    public function testInvalidObjectException2()
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage('Property value is not owned by the object !');

        $myObject = new \stdClass;
        $myObject->id = 1;
        $myObject->invalid = 'value';

        $dico = new PropertyIndexer('id', 'value');

        $dico->add($myObject);
    }

    public function testInvalidKey()
    {
        $this->expectException(UndefinedKeyException::class);

        $this->expectErrorMessage('Undefined index key 1');

        $dico = new PropertyIndexer('id', 'value');
        $dico->get(1);
    }


    public function testPropertyIndexerGetAccessValue()
    {
        $dico = new PropertyIndexer('id', 'value');

        $myObject1 = self::simpleObject(1, 'Nice Value 1');
        $myObject2 = self::simpleObject(2, 'Nice Value 2');
        $myObject3 = self::simpleObject(3, 'Nice Value 3');

        $dico->add($myObject1);
        $dico->add($myObject2);
        $dico->add($myObject3);

        $this->assertEquals(count($dico), 3);
        $this->assertEquals('Nice Value 1', $dico->get(1));
        $this->assertEquals('Nice Value 2', $dico->get(2));
        $this->assertEquals('Nice Value 3', $dico->get(3));
    }


    public function testKeyAccessor()
    {
        $dico = new PropertyIndexer('id', 'value');

        $myObject = self::simpleObject('flat', 'Nice Value from flat object');
        $myObject2 = self::simpleObject('nested', $myObject);

        $dico->add($myObject2);

        $this->assertEquals($myObject, $dico->get('nested'));

        $this->assertEquals('Nice Value from flat object', $dico->get('nested', 'value'));


        $this->assertEquals($myObject, $dico->get($myObject2));

        $this->assertEquals('Nice Value from flat object', $dico->get($myObject2, 'value'));
    }


    public function testPropertyAccessor()
    {
        $dico = new PropertyIndexer('id');

        $childObject = self::simpleObject('child', 'Nice Value from child object');
        $parentObject = self::simpleObject('parent', $childObject);
        $grandFatherObject = self::simpleObject('grandFather', $parentObject);

        $dico->add($grandFatherObject);

        $this->assertEquals($grandFatherObject, $dico->get('grandFather'));
        $this->assertEquals($parentObject, $dico->get('grandFather', 'value'));
        $this->assertEquals($childObject, $dico->get('grandFather', 'value.value'));
        $this->assertEquals('Nice Value from child object', $dico->get('grandFather', 'value.value.value'));

    }


    public function testValuePropertyAccessor()
    {
        $dico = new PropertyIndexer('id');

        $myObject3 = new class
        {
            public $id = "with_method";
            public $value = "Nice value from method object";
            function say()
            {
                return 'hello';
            }
            function isConnected()
            {
                return true;
            }
            function hasHair()
            {
                return true;
            }
        };

        $dico->add($myObject3);

        $this->assertTrue($dico->get('with_method', 'connected'));
        $this->assertTrue($dico->get('with_method', 'hair'));
        $this->assertEquals('hello', $dico->get('with_method', 'say'));
    }



    public function testLoadCollection()
    {
        $dico = new PropertyIndexer('id', 'value');

        $collection = self::getSimpleObjectsCollection(3);
        $dico->load($collection);

        $this->assertEquals(count($dico), 3);
        $this->assertEquals('Nice Value 1', $dico->get(1));
        $this->assertEquals('Nice Value 2', $dico->get(2));
        $this->assertEquals('Nice Value 3', $dico->get(3));
    }

    public function testLoadCollectionOnInvocation()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $dico = new PropertyIndexer('id', 'value', $collection);

        $this->assertEquals(count($dico), 10);
        $this->assertEquals('Nice Value 1', $dico->get(1));
        $this->assertEquals('Nice Value 2', $dico->get(2));
        $this->assertEquals('Nice Value 10', $dico->get(10));
    }

    public function testIter()
    {
        $dico = new PropertyIndexer('id', 'value');

        $collection = self::getSimpleObjectsCollection(3);

        $dico->load($collection);

        $i = 1;
        foreach ($dico as $key => $value) {
            $this->assertEquals("Nice Value " . $i, $value);
            $this->assertEquals($i++, $key);
        }
    }
}
