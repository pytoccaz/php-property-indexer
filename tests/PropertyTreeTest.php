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


use  Obernard\PropertyIndexer\PropertyTree;
use PHPUnit\Framework\TestCase;
use Obernard\PropertyIndexer\Exception\OffsetSetException;


class PropertyBuilderTest extends TestCase
{

    use TestsTrait;


    public function testTreeStructure()
    {
        $collection = self::getSimple3PropertiesObjectCollection(3);

        $this->assertEquals(3, count(new PropertyTree($collection, 'value', 'id1', 'id2')));

        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');


        $this->assertEquals(2, count(new PropertyTree([$object1, $object2], 'value', 'id', 'date')));
        $this->assertEquals(1, count(new PropertyTree([$object1, $object2], 'value', 'date', 'id')));
    }


    public function testArrayAccessImplementation()
    {
        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');
        $object3 = self::simpleObjectWithDate('id3', 'value3');

        // offsetSet
        $tree = new PropertyTree([], 'value', 'id', 'date');
        $this->assertEquals(0, count($tree));

        $tree[] =  $object2;
        $this->assertEquals(1, count($tree));

        $tree[] =  $object1;
        $this->assertEquals(2, count($tree));

        $tree[] =  $object3;
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


        $tree = new PropertyTree([], 'value', 'id', 'date');
        $tree[0] =  $object1;
    }



    public function testIter()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $tree = new PropertyTree($collection, 'value', 'id');

        $i = 1;
        foreach ($tree as $key => $value) {
            $this->assertEquals("Nice Value " . $i, $value);
            $this->assertEquals($i++, $key);
        }
    }

    public function testClosure()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');

        $tree = new PropertyTree([$object1], 'value', function ($item) {
            return $item->id * 2;
        });

        $this->assertTrue(isset($tree[2]));
        $this->assertEquals('value1', $tree[2]);
    }


    public function testLeafClosure()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');

        $tree = new PropertyTree([$object1], function ($item) {
            return $item->id * 2;
        }, function ($item) {
            return $item->id * 2;
        });

        $this->assertTrue(isset($tree[2]));
        $this->assertEquals(2, $tree[2]);
    }

    public function testAccessor()
    {
        $object1 = self::simpleObject('id1', 'value1');
        $object2 = self::simpleObject('id2', 'value2');
        $ptree = new PropertyTree([], 'value', 'id');

        $ptree[] =  $object1;

        $tree = $ptree->getTree();
        $this->assertIsArray($tree);

        $pa = $ptree->getPropertyAccessor();

        $this->assertTrue($pa->isReadable($tree, '[id1]'));
        $this->assertFalse($pa->isReadable($tree, '[id2]'));

        $ptree[] =  $object2;
        $tree = $ptree->getTree();

        $this->assertTrue($pa->isReadable($tree, '[id2]'));
    }

    public function testArrayLeavesStepByStep()
    {
        $object1 = self::simpleObject('id1', 'value1');
        $object2 = self::simpleObject('id2', 'value2');
        $object3 = self::simpleObject('id1', 'value3');

        $ptree = new PropertyTree([], 'value', 'id');
        $ptree->setMode($ptree::ARRAY_LEAF);


        $ptree[] =  $object1;

        $tree = $ptree->getTree();
        $this->assertIsArray($tree);

        $pa = $ptree->getPropertyAccessor();

        $this->assertTrue($pa->isReadable($tree, '[id1]'));
        $this->assertFalse($pa->isReadable($tree, '[id2]'));

        $ptree[] =  $object2;
        $tree = $ptree->getTree();

        $this->assertTrue($pa->isReadable($tree, '[id2]'));


        $ptree[] =  $object3;
        $tree = $ptree->getTree();

        $this->assertEquals(['value1', 'value3'], $tree['id1']);
    }

    public function testBulkArrayLeaves()
    {
        $object1 = self::simpleObject('id1', 'value1');
        $object2 = self::simpleObject('id2', 'value2');
        $object3 = self::simpleObject('id1', 'value3');

        $ptree = new PropertyTree([], 'value', 'id');
        $ptree->setMode($ptree::ARRAY_LEAF);

        $ptree->load([$object1, $object2, $object3]);

        $this->assertEquals(['value1', 'value3'], $ptree['id1']);
        $this->assertEquals(['value2'], $ptree['id2']);
    }


    public function testFlatStructure()
    {
        $collection = self::getSimpleObjectsCollection(3);
        $tree = new PropertyTree($collection, 'value');

        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals("Nice Value " . $i + 1, $tree[$i]);
            // $this->assertEquals($i, $key);
        }
    }
    public function testFlatStructureArrayLeaf()
    {
        $collection = self::getSimpleObjectsCollection(3);

        $tree = new PropertyTree([], 'value');
        $tree->setMode($tree::ARRAY_LEAF);
        $tree->load($collection);

        $this->assertIsArray($tree[0]);
        $this->assertIsArray($tree[1]);
        $this->assertIsArray($tree[2]);
        $this->assertEquals(array(0 => 'Nice Value 3'), $tree[2]);
    }
}
