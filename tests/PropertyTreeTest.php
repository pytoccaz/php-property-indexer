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


use Obernard\PropertyIndexer\PropertyTree;
use PHPUnit\Framework\TestCase;
use Obernard\PropertyIndexer\Exception\OffsetSetException;
use Obernard\PropertyIndexer\Exception\UndefinedModeException;
use Obernard\PropertyIndexer\Exception\InvalidPropertyException;
use Obernard\PropertyIndexer\Exception\InvalidClosureException;


class PropertyBuilderTest extends TestCase
{

    use TestsTrait;


    public function testTreeStructure()
    {
        $collection = self::getSimple3PropertiesObjectCollection(3);

        $this->assertEquals(3, count(new PropertyTree($collection, 'value', ['id1', 'id2'])));

        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');


        $this->assertEquals(2, count(new PropertyTree([$object1, $object2], 'value', ['id', 'date'])));
        $this->assertEquals(1, count(new PropertyTree([$object1, $object2], 'value', ['date', 'id'])));
    }


    public function testArrayAccessImplementation()
    {
        $object1 = self::simpleObjectWithDate('id1', 'value1');
        $object2 = self::simpleObjectWithDate('id2', 'value2');
        $object3 = self::simpleObjectWithDate('id3', 'value3');

        // offsetSet
        $tree = new PropertyTree([], 'value', ['id', 'date']);
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


        $tree = new PropertyTree([], 'value', ['id', 'date']);
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


    public function testIdentityValue()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $tree = new PropertyTree($collection, null, 'id');

        $i = 0;
        foreach ($tree as $key => $value) {
            // $this->assertEquals("Nice Value " . $i, $value);
            $this->assertEquals($collection[$i++], $value);
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


    public function testWithNestedObjects()
    {
        $propertyObject1 = self::simpleObject('flat1', 'Nice Value 1 from flat object');
        $propertyObject2 = self::simpleObject('flat2', 'Nice Value 2 from flat object');


        $obj1 = self::simpleObject('nested', $propertyObject1);

        $obj2 = self::simpleObject('nested', $propertyObject2);

        $ptree = new PropertyTree([$obj1, $obj2], 'value.value', 'value.id');

        $this->assertEquals(array(
            "flat1" => 'Nice Value 1 from flat object',
            "flat2" => 'Nice Value 2 from flat object',
        ), $ptree->getTree());
    }



    public function testOnIterable()
    {
        $collection = self::getSimpleObjectsCollection(10);
        $ptree = new PropertyTree($collection, null);
        $this->assertEquals($collection, $ptree->toArray());
        $ptree2 = new PropertyTree($ptree, null, 'id');
        $ptree3 = new PropertyTree($ptree2, null);

        $this->assertEquals($collection, $ptree3->toArray());
    }

    // public function testPropertiesType()
    // {
    //     $props = ['test', function () {
    //     }];
    //     $test = PropertyTree::checkGoupByPropertyTypes(...$props);

    //     $this->assertTrue($test);
    // }

    public function testGroupByPropertyNotStingOrClosureException()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('GoupByProperties array has to be a list of string|Closure');
        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, [null]);
 
    }

    public function testGroupByPropertyEmptyException()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('GoupByProperties items cannot be empty');
        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, ['']);
 
    }
    public function testGroupByPropertyEmptyException2()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('GoupByProperties items cannot be empty');
        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, '');
    }
    public function testGroupByPropertyEmptyException3()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('GoupByProperties items cannot be empty');
        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, ' ');
    }

    public function testGroupByPropertyEmptyException4()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('GoupByProperties items cannot be empty');
        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, [' ']);
 
    }

    public function testBadModeException()
    {
        $this->expectException(UndefinedModeException::class);

        $collection = self::getSimpleObjectsCollection(2);
        $ptree = new PropertyTree($collection, null, null, 3);
 
    }

    public function testClosureResult()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');

        $this->expectException(InvalidClosureException::class);
        $this->expectExceptionMessage('The stringyfied value has to be non-empty');

        $tree = new PropertyTree([$object1], null, function ($item) {
            return null;
        });
 
    }

    public function testClosureResult2()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');

        $this->expectException(InvalidClosureException::class);
        $this->expectExceptionMessage('The stringyfied value has to be non-empty');

        $tree = new PropertyTree([$object1], null, function ($item) {
            return false;
        });
 
    }


    public function testClosureResult3()
    {
        $object1 = self::simpleObjectWithDate(1, 'value1');
        $this->expectException(InvalidClosureException::class);
        $this->expectExceptionMessage('The closure has to return a stringable value');

        $tree = new PropertyTree([$object1], null, function ($item) {
            return $item;
        });
 
    }
}
