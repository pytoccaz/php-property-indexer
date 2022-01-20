# 🖇 Porperty Indexer

Indexes objects/arrays properties inside a key-value map.
Based on symfony/property-access.


## Installation

```shell
// from packagist
composer require obernard/property-indexer


// or from github
git clone git@github.com:pytoccaz/php-property-indexer.git
```

## Usage  

PorpertyIndexer builds a key-value dictionary by retriving key and value from objects or arrays.


### Index from objects

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

$index->get('id1') //  returns "value1"
$index->get('id2') //  returns "value2"

```
### Index from arrays

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
retreive indexed values:
```php
$index->get('id1') //  returns "value1"
$index->get('id2') //  returns "value2"
```

### Bulk Load from collections
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
## Tests

Run `composer test`.

### Index Objects or Arrays (not properties)  

Don't specify the value path to index the objects:
```php 
$objectIndexer = new Obernard\PropertyIndexer\PropertyIndexer('id');
$arrayIndexer = new Obernard\PropertyIndexer\PropertyIndexer('[id]');
```


## Contributing

Feel free to submit pull requests.

## Licence

MIT

Copyright (c) 2021 Olivier BERNARD