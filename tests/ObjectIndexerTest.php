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

    private static function simpleObject(int|string $id, mixed $value): \stdClass
    {
        $object = new \stdClass;
        $object->value = $value;
        $object->id = $id;

        return $object;
    }


    private static function getSimpleObjectsCollection(int $n): array
    {
        $collection = [];
        for ($i = 1; $i <= $n; $i++) {
            $collection[] = self::simpleObject($i, "Nice Value " . $i);
        }
        return $collection;
    }


    public function testFillIndexer()
    {
        // create an obj indexer obj.id => obj.value
        $dico = new PropertyIndexer('id', 'value');
        $this->assertEquals(count($dico), 0);

        $myObject = self::simpleObject(1, 'Nice Value');

        $dico->add($myObject);

        $this->assertEquals(count($dico), 1);
        $this->assertEquals($dico->get(1), 'Nice Value');

        $dico->add($myObject);
        $this->assertEquals(count($dico), 1);
        $this->assertEquals($dico->get(1), 'Nice Value');

        $myObject2 = self::simpleObject('azerty', $myObject);


        $dico->add($myObject2);

        $this->assertEquals(count($dico), 2);
        $this->assertEquals($dico->get('azerty'),  $myObject);
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
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(3), 'Nice Value 3');
    }


    public function testKeyAccessor()
    {
        $dico = new PropertyIndexer('id', 'value');

        $myObject = self::simpleObject('flat', 'Nice Value from flat object');
        $myObject2 = self::simpleObject('nested', $myObject);

        $dico->add($myObject2);

        $this->assertEquals($dico->get('nested'),  $myObject);

        $this->assertEquals($dico->get('nested', 'value'), 'Nice Value from flat object');


        $this->assertEquals($dico->get($myObject2),  $myObject);

        $this->assertEquals($dico->get($myObject2, 'value'), 'Nice Value from flat object');
    }


    public function testPropertyAccessor()
    {
        $dico = new PropertyIndexer('id');

        $childObject = self::simpleObject('child', 'Nice Value from child object');
        $parentObject = self::simpleObject('parent', $childObject);
        $grandFatherObject = self::simpleObject('grandFather', $parentObject);

        $dico->add($grandFatherObject);

        $this->assertEquals($dico->get('grandFather'),  $grandFatherObject);
        $this->assertEquals($dico->get('grandFather', 'value'),  $parentObject);
        $this->assertEquals($dico->get('grandFather', 'value.value'),  $childObject);
        $this->assertEquals($dico->get('grandFather', 'value.value.value'),  'Nice Value from child object');

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
        $this->assertEquals($dico->get('with_method', 'say'), 'hello');
    }



    public function testLoadCollection()
    {
        $dico = new PropertyIndexer('id', 'value');

        $collection = self::getSimpleObjectsCollection(3);
        $dico->load($collection);

        $this->assertEquals(count($dico), 3);
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(3), 'Nice Value 3');
    }

    public function testLoadCollectionOnInvocation()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $dico = new PropertyIndexer('id', 'value', $collection);

        $this->assertEquals(count($dico), 10);
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(10), 'Nice Value 10');
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
