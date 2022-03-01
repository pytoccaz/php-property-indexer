# 🖇 Porperty Indexer & Tree builder

A `PorpertyIndexer` indexes objects/arrays properties inside a key-value map.

*key* and *value* values are retreived from objects or arrays via [symfony/property-access](https://symfony.com/doc/current/components/property_access.html#usage) component.



A `PorpertyTreeBuilder` builds a tree-like structure from objects properties inside a collection.


`PorpertyIndexer` and `PropertyTree` are iterables. 


## Installation

```shell
# from packagist
composer require obernard/property-indexer


# or from github
git clone git@github.com:pytoccaz/php-property-indexer.git
```

## Porperty Indexer

### Index objects properties

Say you have objects with properties `id` and `value`:
```php
$object1 = new \stdClass;
$object1->id = "id1";
$object1->value = "value1";

$object2 = new \stdClass;
$object2->id = "id2";
$object2->value = "value2";

```

Create an indexer for *id&value* objects :
```php
$indexer = new Obernard\PropertyIndexer\PropertyIndexer('id', 'value');
```

Add objects:
```php
$indexer->add($object1)->add($object2);
```

Retreive indexed values via their key:
```php
$index->get('id1') //  returns "value1"
$index->get('id2') //  returns "value2"
```

Or directly via a reference to an indexed object:
```php
$index->get($object2) //  returns "value2"
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

Say you have objects with properties `id`, `value` and `date`:
```php
$object1 = new \stdClass;
$object1->id = "id1";
$object1->value = "value1";
$object1->date = "today";

$object2 = new \stdClass;
$object2->id = "id2";
$object2->value = "value2";
$object2->date = "today";

// first arg is the collection of objects
// second arg is the "leaves" value
// the rest of args is a groupBy-like definition of the tree levels
$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', 'id', 'date');
       
var_dump($tree);
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

$tree = new Obernard\PropertyIndexer\PropertyTree([$obj1, $obj2], 'value', 'date', 'id');
var_dump($tree);
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



## Tests 

Run `composer test`.


## Contributing

Feel free to submit pull requests.

## Licence

MIT

Copyright (c) 2022 Olivier BERNARD