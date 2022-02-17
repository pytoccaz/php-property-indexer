<?php
/*
 * This file is part of the Obernard package.
 *
 * (c) Olivier Bernard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Obernard\PropertyIndexer;

/**
 * Given a collection of arrays/objets, builds a tree with leaves values and path provided by the items in the collection. 
 * 
 * @author olivier Bernard
 */


class PropertyTreeBuilder extends PropertyPicker implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * 
     * As an example, say we have a collection of 3 objects Obj { prop1, prop2, prop3 }:
     * 
     * collection = [
     *  obj1 = { prop1:"val1a", prop2:"val2a", prop3:"val3a" }
     *  obj2 = { prop1:"val1a", prop2:"val2b", prop3:"val3b" }
     *  obj3 = { prop1:"val3a", prop2:"val2a", prop3:"val3a" }
     * ]
     * 
     * 
     * 
     * To build a tree with prop3 values as leaves, prop1 as node of level 0, prop2 as node of level 1,
     *   invoke PropertyTreeBuilder(collection, prop3, prop1, prop2)
     * 
     *   resultingtree = [
     *      "val1a" => ["val2a" => "val3a", "val2b" => val3b],
     *      "val3a" => ["val2a" => "val3a"]
     *   ]
     * 
     * To build a tree with prop3 values as leaves, prop1 as node of level 0 
     *   invoke PropertyTreeBuilder(collection, prop3, prop1)
     * 
     *   resultingtree = [
     *      "val1a" => [ "val3b" ], // the last leaf with "val1a" path is kept
     *      "val3a" => [ "val3a" ]
     *   ]
     * 
     * Note that 2 identical path result in the last leaf to be kept. 
     * 
     * Property path have to be compatible with Symfony PropertyAccess path.
     *  https://symfony.com/doc/current/components/property_access.html
     * 
     * 
     */

    /**
     * @var array $groupByProperties list of properties defining the structure of the tree from the root to the leaves
     */
    private array $groupByProperties;

    /**
     * @var string $valuePath Compatible PropertyAccess path inside objects for retriving leaves values
     */
    private ?string $valuePath;


    /**
     * @var array root of the classifier tree 
     */
    private $tree = [];



    /**
     * @param string $valuePath Path of the property inside added objects/arrays providing a key value
     * @param string $groupByProperties  Path of the properties inside added objects/arrays whose values define the complete leaf path inside the tree
     * @param $collection Collection of compatible objects/arrays to load.
     * 
     */
    public function __construct(array $collection, ?string $valuePath = null, string ...$groupByProperties)
    {
        parent::__construct();

        $this->groupByProperties = $groupByProperties;
        $this->valuePath = $valuePath;

        $this->load($collection);
 
    }

    /**
     * Loads a collection of compatible objects/arrays
     */
    public function load(iterable $collection): self
    {
        foreach ($collection as $item) {
            // extract the leaf value
            $leaf = $this->getValueFromObject($item);

            // build the leaf path
            $leafPath = self::createPropertyPath();

            if (empty($this->groupByProperties))
                // just push the leaves in a flat tree
                $leafPath->appendIndex($this->count());
            else
                foreach ($this->groupByProperties as $path) {
                    // concat the path edges
                    $leafPath->appendIndex(self::getPropertyFromObject($item, $path));
                }

            // write the leaf
            self::setValue($this->tree, $leafPath, $leaf);
        }

        return $this;
    }


    private function getValueFromObject(object|array $object): mixed
    {
        if (!$this->valuePath)
            return $object;

        return self::getPropertyFromObject($object, $this->valuePath);
    }



    /**
     *  Returns all the keys ok level 0
     */
    public function keys(): array
    {
        return array_keys($this->tree);
    }

    /**
     *   Countable interface implementation
     */
    public function count(): int
    {
        // count the keys of level 0
        return count($this->tree);
    }

    /**
     *   IteratorAggregate interface implementation
     */
    public function getIterator(): \Traversable
    {
        yield from $this->tree;
    }

    /**
     *   ArrayAccess interface implementation
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->tree[$offset]);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->tree[$offset];
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->tree[$offset]);
    }
    public function offsetSet($offset, $value):void {
        if (!is_null($offset)) {
            throw new Exception\OffsetSetException("Offset must be null");
        } else {
            $this->load([$value]);
        }
    }
 
}
