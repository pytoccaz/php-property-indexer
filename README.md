# 🖇 Porperty Indexer & Tree builder

* A `PorpertyIndexer` indexes objects/arrays properties inside a key-value map.

* A `PorpertyTree` builds a Tree structure from a collection of objects.


`PorpertyIndexer` and `PropertyTree` are iterables. 

Index *key* and *value* values one case,  nodes and leaves in the other are retreived from objects or arrays via [symfony/property-access](https://symfony.com/doc/current/components/property_access.html#usage) component.


## Installation

```shell
# from packagist
composer require obernard/property-indexer


# or from github
git clone git@github.com:pytoccaz/php-property-indexer.git
```

## Porperty Indexer

A `PorpertyIndexer` indexes objects/arrays properties inside a key-value map.

Choose :
* a string|int property to provide keys
* a mixed property or the objects|arrays themselves to provide values


### Index objects properties

Say you have objects with properties `id` and `value`:
```php
$obj1->id == "id1";
$obj1->value == "value1";

$obj2->id == "id2";
$obj2->value == "value2";
```

Create an indexer for *id&value* objects :
```php
$indexer = new Obernard\PropertyIndexer\PropertyIndexer('id', 'value');
```

Add objects:
```php
$indexer->add($obj1)->add($obj2);
```

Retreive indexed values via their key:
```php
$index->get('id1') //  returns "value1"
$index->get('id2') //  returns "value2"
```

Or directly via a reference to an indexed object:
```php
$index->get($obj2) //  returns "value2"
```

### Index array properties

Say you have array maps with `id` and `value` keys:
```php
$array1= ["id" => "id1", "value" => "value1"];

$array2 = ["id" => "id2", "value" => "value2"];
```

Create an indexer for *id&value* arrays (note the use of brakets for array property path): 
```php
$indexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]', '[value]');
```

Add arrays:
```php
$indexer->add($array1)->add($array2);
```
Retreive indexed values via their key:
```php
$index->get('id1') //  returns "value1"
$index->get('id2') //  returns "value2"
```
 
### Bulk Load collections

Use the `load` method:

```php 
$indexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]', '[value]');
$collection = [
            ["id" => "id1", "value" => "value1"],
            ["id" => "id2", "value" => "value2"]
]            
$indexer = $indexer->load($collection);
$index->get('id1') //  returns value1
$index->get('id2') //  returns value2
```

Or invoke `PropertyIndexer` with a third argument:

```php
$indexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]', '[value]', $collection);
```


### Index Objects or Arrays (not their properties)  

Don't specify the value path or set it to null when invoking `PropertyIndexer`:
```php 
$objectIndexer = new Obernard\PropertyIndexer\PropertyIndexer('id');
$arrayIndexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]', null);
```

## Porperty Tree Builder

A `PorpertyTree` builds a Tree structure from a collection of objects.

Leaves path and values are picked from the items of the processed collection.


### Porperty Tree basics 


Say you have objects with properties `id`, `value` and `date`:
```php
$obj1->id == "id1";
$obj1->value == "value1";
$obj1->date == "today";

$obj2->id == "id2";
$obj2->value == "value2";
$obj2->date == "today"; 

// first arg is the collection of objects
// second arg is the "leaves" value
// third arg is a groupBy-like definition of the tree levels
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', ['id', 'date']);
//   ["tree":"Obernard\PropertyIndexer\PropertyTree":private]=>
//   array(2) {
//     ["id1"]=>
//     array(1) {
//       ["today"]=>
//       string(6) "value1"
//     }
//     ["id2"]=>
//     array(1) {
//       ["today"]=>
//       string(6) "value2"
//     }
//   }

$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', ['date', 'id']);
//   ["tree":"Obernard\PropertyIndexer\PropertyTree":private]=>
//   array(1) {
//     ["today"]=>
//     array(2) {
//       ["id1"]=>
//       string(6) "value1"
//       ["id2"]=>
//       string(6) "value2"
//     }
//   }
```

### Porperty Tree mode ARRAY_LEAF 

Mind the possible colisions between objects sharing the "same" properties path:

``` php
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', ['date']);
//   ["tree":"Obernard\PropertyIndexer\PropertyTree":private]=>
//   array(1) {
//     ["today"]=>
//     string(6) "value2"
//   }
```
To avoid possible colisions, switch to array-type leaves:

```php
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', ['date'], PropertyTree::ARRAY_LEAF);
//   ["tree":"Obernard\PropertyIndexer\PropertyTree":private]=>
//   array(1) {
//     ["today"]=>
//     array(2) {
//       [0]=>
//       string(6) "value1"
//       [1]=>
//       string(6) "value2"
//     }
//   }
```

### Porperty Tree advanced features

### Closure as valuePath  

`$valuePath` 2nd arg accepts a one-arg Closure (that must return int|string). While iterating the object|array collection, PorpertyTree will bind the arg with the current object|array item to extract a value.

To retrieve the object items themselves, you may use an Identity Closure ( or better set `valuePath` to `null` :P ) :

```php
// Use an Idendity Closure to retrieve Objects themselves: 
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], function($item):string {return $item;}, ['id']);
  ["tree":"Obernard\PropertyIndexer\PropertyTree":private]=>
//   array(2) {
//     ["id1"]=>
//     object(stdClass)#106 (3) {
//       ["id"]=>
//       string(3) "id1"
//       ["value"]=>
//       string(6) "value1"
//       ["date"]=>
//       string(5) "today"
//     }
//     ["id2"]=>
//     object(stdClass)#83 (3) {
//       ["id"]=>
//       string(3) "id2"
//       ["value"]=>
//       string(6) "value2"
//       ["date"]=>
//       string(5) "today"
//     }
//   }


// Or a simple way :
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], null, ['id']);
```

### Closure as groupByproperties  

array `$groupByproperties` 3nd arg accepts one-arg Closures (that must return int|string). While iterating the object|array collection, PorpertyTree will bind the arg with the current object|array item to extract a tree-path node :  

```php
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], null, [function($item):string {return $item->id;}]);

// is a complicated way to get the same resulting tree as : 
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], null, ['id']);
```

Those 2 examples are dumb-ones. Closures are for performing complex tasks (to retrieve path or leaves) that objects properties do not provide natively.

## Tests 

Run `composer test`.


## Contributing

Feel free to submit pull requests.

## Licence

MIT

Copyright (c) 2022 Olivier BERNARD