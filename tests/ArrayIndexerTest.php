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
use Obernard\PropertyIndexer\Exception\InvalidArrayException;
use Obernard\PropertyIndexer\Exception\UndefinedKeyException;
use Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException;


class ArrayIndexerTest extends TestCase
{

 
    private static function simpleArray(int|string $id, mixed $value): array
    {

        return ['id' => $id, 'value' => $value];
    }

 
    private static function getSimpleArraysCollection(int $n): array
    {
        $collection = [];
        for ($i=1;$i<=$n;$i++) {
            $collection[] = self::simpleArray($i, "Nice Value " . $i);
        }
        return $collection;
    }


    public function testIndexerFilledWithArrays()
    {
        // create an obj indexer obj.id => obj.value
        $dico = new PropertyIndexer('[id]', '[value]');
        $this->assertEquals(count($dico), 0);

        // 
        $myArray = self::simpleArray(1, 'Nice Value');

        $dico->add($myArray);

        $this->assertEquals(count($dico), 1);
        $this->assertEquals($dico->get(1), 'Nice Value');

        $dico->add($myArray);
        $this->assertEquals(count($dico), 1);
        $this->assertEquals($dico->get(1), 'Nice Value');

        $myArray2 = self::simpleArray('azerty', $myArray);


        $dico->add($myArray2);

        $this->assertEquals(count($dico), 2);
        $this->assertEquals($dico->get('azerty'),  $myArray);
    }


    
    public function testValidArray()
    {

        $dico = new PropertyIndexer('[id]', '[value]');

        $myArray = self::simpleArray(1, 'Nice Value');

        $this->assertTrue($dico->isValid($myArray));
    }

    public function testInvalidArray()
    {

        $dico = new PropertyIndexer('[identity]', '[content]');

        $myArray = self::simpleArray(1, 'Nice Value');

        $this->assertFalse($dico->isValid($myArray));
    }

    // public function testInvalidObject()
    // {
    //     $this->expectException(InvalidObjectException::class);
    //     $this->expectExceptionMessage('Property missingProperty is not owned by the object !');

    //     $myObject = self::simpleObject(1, 'Nice Value');
    //     $dico = new PropertyIndexer('[id]', '[value]');

    //     $this->assertTrue($dico->objectValidator($myObject, 'missingProperty'));
    // }


    public function testInvalidKey()
    {
        // $this->expectError();
        $this->expectException(UndefinedKeyException::class);

        $this->expectErrorMessage('Undefined index key 1');

        $dico = new PropertyIndexer('[id]', '[value]');
        $dico->get(1);
    }

    public function testInvalidArrayException()
    {
        $this->expectException(InvalidArrayException::class);
        $this->expectExceptionMessage('Property [id] is not owned by the array !');

        $myArray = ['invalidKey' => 1, 'value' => 'Nice Value'];
        $dico = new PropertyIndexer('[id]', 'value');

        $dico->add($myArray);

    }
    // public function testInvalidPropertyPath()
    // {
    //     $this->expectException(InvalidPropertyPathException::class);
    //     // $this->expectExceptionMessage('Property [missingProperty] is not owned by the object !');

    //     $myArray = ['id' => 1, 'value' => 'Nice Value'];
    //     $dico = new PropertyIndexer('id', 'value');

    //     $this->assertTrue($dico->objectValidator($myArray, '.[missingProperty]'));
    // }

    public function testPropertyIndexerGetAccessValue()
    {
        $dico = new PropertyIndexer('[id]', '[value]');

        $array1 = self::simpleArray(1, 'Nice Value 1');
        $array2 = self::simpleArray(2, 'Nice Value 2');
        $array3 = self::simpleArray(3, 'Nice Value 3');

        $dico->add($array1);
        $dico->add($array2);
        $dico->add($array3);

        $this->assertEquals(count($dico), 3);
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(3), 'Nice Value 3');
    }


    public function testKeyAccessor()
    {
        $dico = new PropertyIndexer('[id]', '[value]');

        $myArray = self::simpleArray('flat', 'Nice Value from flat object');
        $myArray2 = self::simpleArray('nested', $myArray);

        $dico->add($myArray2);

        $this->assertEquals($dico->get('nested'),  $myArray);

        $this->assertEquals($dico->get('nested', '[value]'), 'Nice Value from flat object');


        $this->assertEquals($dico->get($myArray2),  $myArray);

        $this->assertEquals($dico->get($myArray2, '[value]'), 'Nice Value from flat object');
    }


    public function testLoadCollection()
    {
        $dico = new PropertyIndexer('[id]', '[value]');

        $collection = self::getSimpleArraysCollection(3);
        $dico->load($collection);

        $this->assertEquals(count($dico), 3);
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(3), 'Nice Value 3');
    }

    public function testLoadCollectionOnInvocation()
    {
        $collection = self::getSimpleArraysCollection(10);
        $dico = new PropertyIndexer('[id]', '[value]', $collection);

        $this->assertEquals(count($dico), 10);
        $this->assertEquals($dico->get(1), 'Nice Value 1');
        $this->assertEquals($dico->get(2), 'Nice Value 2');
        $this->assertEquals($dico->get(10), 'Nice Value 10');
    }

    public function testIter()
    {
        $dico = new PropertyIndexer('[id]', '[value]');

        $collection = self::getSimpleArraysCollection(3);

        $dico->load($collection);

        $i = 1; 
        foreach($dico as $key => $value) {
            $this->assertEquals("Nice Value " . $i, $value);
            $this->assertEquals($i++, $key);
        }
    }


}
