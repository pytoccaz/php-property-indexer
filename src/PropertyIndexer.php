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
 * Indexes objects/arrays properties inside a key-value map. 
 * 
 * @author olivier Bernard
 */


class PropertyIndexer extends PropertyPicker implements \Countable, \IteratorAggregate
{
    /**
     * A Key-value mapper with:
     *     - an add($objectOrArray) method to add key-value pairs
     *     - a get($key) method to retrieve the associated values or their properties
     *
     * The path of key and value are specified when invoking the class with object property path when 
     * dealing with objects:
     *      $indexer = new PropertyIndexer('path.to.id', 'path.to.value');
     *      $object->path->to->id // 'myId'
     *      $pbject->path->to->value // 'MyValue'
     * 
     *      $indexer->add($object) // pushes ['myId' => 'MyValue'] to the indexer
     *      $indexer->get('myId') // returns 'MyValue' from the indexer
     *  
     * The path of key and value are specified when invoking the class with array property path when 
     * dealing with arrays:
     *      $indexer = new PropertyIndexer('[path][to][id]', '[path][to][value]');
     *      $array["path"]["to"]["id"] // 'myId'
     *      $array["path"]["to"]["value"] // 'MyValue'
     * 
     *      $indexer->add($object) // pushes ['myId' => 'MyValue'] to the indexer
     *      $indexer->get('myId') // returns 'MyValue' from the indexer
     * 
     * Key and Value path has to be compatible with Symfony PropertyAccess path.
     *  https://symfony.com/doc/current/components/property_access.html
     * 
     * 
     * @throws UndefinedKeynException When trying to get a non-existing a key  
     * @throws InvalidObjectException When trying to add an object/array that has no property verifying the key path          
     */

 

    /**
     * @var string $keyPath Compatible PropertyAccess path inside added objects for retriving key values
     */
    private string $keyPath;

    /**
     * @var string $valuePath Compatible PropertyAccess path inside added objects for retriving value values
     */
    private ?string $valuePath;

    /**
     * @var array dictionary of indexed Objects properties 
     */
    private $index = [];


    /**
     * @param string $keyPath Path of the property inside added objects/arrays providing a key value
     * @param string $valuePath Path of the property inside added objects/arrays providing the value associated to the key
     * @param $collection Collection of compatible objects/arrays to load.
     * 
     */
    public function __construct(string $keyPath, ?string $valuePath = null, ?array $collection = null)
    {
        parent::__construct();

        $this->keyPath = $keyPath;
        $this->valuePath = $valuePath;

        if ($collection) {
            $this->load($collection);
        }
    }

    private function setKeyValue(string|int $key, mixed $value): self
    {
        $this->index[$key] = $value;
        return $this;
    }

 
    /**
     * Loads a collection of compatible objects/arrays
     */
    public function load(iterable $collection): self
    {
        foreach ($collection as $item) {
            $this->add($item);
        }
        return $this;
    }
 

    private function getValueFromObject(object|array $object): mixed
    {
        if (!$this->valuePath)
            return $object;

        return self::getPropertyFromObject($object, $this->valuePath);
    }


    private function getKeyFromObject(object|array $object): string|int
    {
        return self::getPropertyFromObject($object, $this->keyPath);
    }

    /**
     * Add an entry inside the indexer
     */
    public function add(object|array $object): self
    {
        $this->setKeyValue(
            $this->getKeyFromObject($object),
            $this->getValueFromObject($object)
        );
        return $this;
    }

    /**
     * Retrieves a value (or a child-property of the value if $property is specified) associated with a key.
     * If key is an object, attemps to get a valid key from it.
     */
    public function get(string|int|object|array $key, ?string $property = null): mixed
    {
        if (is_object($key) || is_array($key))
            $key = $this->getKeyFromObject($key);

        if (!array_key_exists($key, $this->index))
            throw new Exception\UndefinedKeyException(sprintf("Undefined index key %s",  $key));
        $item = $this->index[$key];

        if (!$property)
            return $item;
        else if (is_object($item) || is_array($item))
            return $this->getPropertyFromObject($item, $property);
        else
            throw new Exception\InvalidObjectException('Item does not support property access');
    }

    /**
     *  Removes an item form the indexer
     */
    public function remove(string|int|object|array $key): void
    {
        if (is_object($key) || is_array($key))
            $key = $this->getKeyFromObject($key);

        unset($this->index[$key]);
    }

    /**
     *  Returns all the keys
     */
    public function keys(): array
    {
        return array_keys($this->index);
    }
 
    /**
     *  Returns true if the object|array is compatible with the indexer
     */
    public function isValid(object|array $object): bool
    {
        try {
            return self::objectOrArrayValidator($object, $this->keyPath, $this->keyPath);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }


    /**
     *   Countable interface implementation
     */
    public function count(): int
    {
        return count($this->index);
    }

    /**
     *   IteratorAggregate interface implementation
     */
    public function getIterator(): \Traversable
    {
        yield from $this->index;
    }
}
