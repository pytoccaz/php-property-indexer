<?php

namespace Obernard\PropertyIndexer\Tests;

Trait TestsTrait
{

    private static function simpleArray(int|string $id, mixed $value): array
    {

        return ['id' => $id, 'value' => $value];
    }

 
    private static function getSimpleArraysCollection(int $n): array
    {
        $collection = [];
        for ($i=1;$i<=$n;$i++) {
            $collection[] = self::simpleArray($i, "Nice Value " . $i);
        }
        return $collection;
    }



    private static function simpleObject(int|string $id, mixed $value): \stdClass
    {
        $object = new \stdClass;
        $object->value = $value;
        $object->id = $id;

        return $object;
    }



    private static function getSimpleObjectsCollection(int $n): array
    {
        $collection = [];
        for ($i = 1; $i <= $n; $i++) {
            $collection[] = self::simpleObject($i, "Nice Value " . $i);
        }
        return $collection;
    }


    private static function simpleObjectWithDate(int|string $id, mixed $value, string $date = 'today'): \stdClass
    {
        $object = new \stdClass;
        $object->value = $value;
        $object->id = $id;
        $object->date = $date;

        return $object;
    }

    private static function simple3PropertiesObject(int|string $id1, int $id2, mixed $value): \stdClass
    {
        $object = new \stdClass;
        $object->value = $value;
        $object->id1 = $id1;
        $object->id2 = $id2;

        return $object;
    }


    private static function getSimple3PropertiesObjectCollection(int $n): array
    {
        $collection = [];
        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $n; $j++)
                $collection[] = self::simple3PropertiesObject($i, $j, "Nice Value $i $j");
        }
        return $collection;
    }

}


