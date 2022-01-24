# 🖇 Porperty Indexer

A `PorpertyIndexer` indexes objects/arrays properties inside a key-value map.

*key* and *value* values are retreived from objects or arrays via symfony/property-access component.

`PorpertyIndexer` are iterable. 


## Installation

```shell
# from packagist
composer require obernard/property-indexer


# or from github
git clone git@github.com:pytoccaz/php-property-indexer.git
```

## Index objects properties

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

## Index array properties

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
 
## Bulk Load collections

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


## Index Objects or Arrays (not their properties)  

Don't specify the value path or set it to null when invoking `PropertyIndexer`:
```php 
$objectIndexer = new Obernard\PropertyIndexer\PropertyIndexer('id');
$arrayIndexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]', null);
```

## Tests

Run `composer test`.


## Contributing

Feel free to submit pull requests.

## Licence

MIT

Copyright (c) 2022 Olivier BERNARD