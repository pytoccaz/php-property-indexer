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
 * Given a collection of arrays|objets, builds a tree with leaves values and path provided by the items in the collection. 
 * 
 * @author olivier Bernard
 */


class PropertyTree extends PropertyPicker implements \Countable, \IteratorAggregate, \ArrayAccess
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
     *   invoke PropertyTree(collection, prop3, [prop1, prop2])
     * 
     *   resultingTree = [
     *      "val1a" => ["val2a" => "val3a", "val2b" => val3b],
     *      "val3a" => ["val2a" => "val3a"]
     *   ]
     * 
     * To build a tree with prop3 values as leaves, prop1 as node of level 0 
     *   invoke PropertyTree(collection, prop3, prop1)
     * 
     *   resultingTree = [
     *      "val1a" => [ "val3b" ], // the last object with "val1a" path is kept
     *      "val3a" => [ "val3a" ]
     *   ]
     * 
     * Note that 2 identical path result in the last leaf to be kept whith SCALAR_LEAF mode on . 
     * 
     * Property path have to be compatible with Symfony PropertyAccess path.
     *  https://symfony.com/doc/current/components/property_access.html
     * 
     * 
     */

    // leaf $mode values
    const SCALAR_LEAF = 1;
    const ARRAY_LEAF = 2;

    /**
     * list of properties defining the the leaves path.  
     * @var list<string|\Closure>  
     */
    private array $groupByProperties = [];

    /** 
     * Compatible PropertyAccess path inside objects for retriving leaves values.
     * @var string|\Closure|null 
     */
    private $valuePath;


    /**
     * root of the property tree
     * @var array  
     */
    private $tree = [];


    /**
     * type of leaves ( self::SCALAR_LEAF or self::ARRAY_LEAF )
     * @var int 
     */

    private $mode;

    /**
     * @param iterable $collection Collection of compatible objects/arrays to load.
     * @param string|\Closure|null $valuePath Path of the property inside added objects/arrays providing a leaf value
     * @param list<string|\Closure>|string|\Closure|null $groupByProperties Path of the properties inside added objects/arrays whose values define the leaves path inside the tree
     * 
     */
    public function __construct(iterable $collection, string|\Closure|null $valuePath = null, array|string|\Closure|null $groupByProperties = null, $mode = self::SCALAR_LEAF)
    {


        parent::__construct();

        if (is_array($groupByProperties)) {
            self::checkGoupByPropertyTypes(...$groupByProperties);
            $this->groupByProperties = $groupByProperties;
        } else if (!!$groupByProperties)
            $this->groupByProperties[] = $groupByProperties;

        $this->valuePath = $valuePath;
        $this->setMode($mode);
        $this->load($collection);
    }

    /**
     * Loads a collection of compatible objects/arrays
     */
    public function load(iterable $collection = []): self
    {
        foreach ($collection as $item) {
            // extract the leaf value
            $leaf = $this->getValueFromObject($item);

            // build the leaf path
            $leafPath = self::createPropertyPath();

            if (empty($this->groupByProperties))
                // just push the leaves in a flat array
                $leafPath->appendIndex($this->count());
            else
                foreach ($this->groupByProperties as $path) {

                    if (is_string($path))
                        // concat the path edges
                        $leafPath->appendIndex(self::getPropertyFromObject($item, $path));

                    else
                        // call the closure on the object item and append the result to the path definition
                        $leafPath->appendIndex($path($item));
                }

            // write the leaf

            if ($this->mode === self::SCALAR_LEAF) {
                self::setValue($this->tree, $leafPath, $leaf);
            } else if ($this->mode === self::ARRAY_LEAF) {
                if (self::isReadable($this->tree, $leafPath)) {
                    // append value to existing leaf
                    $newLeaf = self::getValue($this->tree, $leafPath);
                    $newLeaf[] = $leaf;
                    self::setValue($this->tree, $leafPath, $newLeaf);
                } else {
                    // create new leaf
                    self::setValue($this->tree, $leafPath, array($leaf));
                }
            } else {
                throw new Exception\UndefinedModeException("Unsupported mode");
            }
        }


        return $this;
    }


    private function getValueFromObject(object|array $object): mixed
    {
        if ($this->valuePath === null)
            return $object;
        else if ($this->valuePath instanceof \Closure) {
            $closure = $this->valuePath;
            return ($closure($object));
        } else
            return self::getPropertyFromObject($object, $this->valuePath);
    }

    public function toArray(): array
    {
        return $this->tree;
    }

    public function getTree(): array
    {
        return $this->tree;
    }


    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     *  Returns all the keys of level 0
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
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->tree[$offset]);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->tree[$offset];
    }
    public function offsetUnset(mixed $offset): void
    {
        unset($this->tree[$offset]);
    }
    public function offsetSet($offset, $value): void
    {
        if (!is_null($offset)) {
            throw new Exception\OffsetSetException("Offset must be null");
        } else {
            $this->load([$value]);
        }
    }

    public static function checkGoupByPropertyTypes(string|\Closure ...$groupByProperties): bool
    {
        return true;
    }
}
