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


use  Obernard\PropertyIndexer\PropertyTreeBuilder;
use PHPUnit\Framework\TestCase;
use Obernard\PropertyIndexer\Exception\OffsetSetException;


class PropertyBuilderTest extends TestCase
{

    use TestsTrait;


    public function testTreeStructure()
    {
        $collection=self::getSimple3PropertiesObjectCollection(3);

        $this->assertEquals(3, count(new PropertyTreeBuilder($collection, 'value', 'id1', 'id2')));

        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');

  
        $this->assertEquals(2, count(new PropertyTreeBuilder([$object1, $object2], 'value', 'id', 'date')));
        $this->assertEquals(1, count(new PropertyTreeBuilder([$object1, $object2], 'value', 'date', 'id')));

    }


    public function testArrayAccessImplementation()
    {
        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');
        $object3 = self::simpleObjectWithDate('id3', 'value3');

        // offsetSet
        $tree = new PropertyTreeBuilder([ ], 'value', 'id', 'date');
        $this->assertEquals(0, count($tree));

        $tree[] =  $object2 ;
        $this->assertEquals(1, count($tree));

        $tree[] =  $object1 ;
        $this->assertEquals(2, count($tree));

        $tree[] =  $object3 ;
        $this->assertEquals(3, count($tree));

        // offsetUnset
        unset($tree["id3"]);
        $this->assertEquals(2, count($tree));

        // offsetGet
        $this->assertEquals(["today" => "value1"], $tree["id1"]);

        // OffssetExists
        $this->assertTrue(isset($tree["id1"]));


    }

    public function testInvalidOffset()
    {
        $this->expectException(OffsetSetException::class);

        $object1 = self::simpleObjectWithDate('id1', 'value1');


        $tree = new PropertyTreeBuilder([ ], 'value', 'id', 'date');
        $tree[0] =  $object1 ;
    }



    public function testIter()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $tree = new PropertyTreeBuilder($collection, 'value', 'id');

        $i = 1;
        foreach ($tree as $key => $value) {
            $this->assertEquals("Nice Value " . $i, $value);
            $this->assertEquals($i++, $key);
        }
    }

    public function testClosure()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');

        $tree = new PropertyTreeBuilder([$object1], 'value', function($item) { return $item->id*2; });
        
        $this->assertTrue(isset($tree[2]));   
        $this->assertEquals('value1', $tree[2]);
    }
}
 