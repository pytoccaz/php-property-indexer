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
 * Property picker class interfacing Symfony PropertyAccessor
 * @see https://symfony.com/doc/current/components/property_access.html for path syntax
 * @author olivier Bernard
 */

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;
use Symfony\Component\PropertyAccess\PropertyPathInterface;


class PropertyPicker  
{

    /**
     * @var PropertyAccessor $pa For retriving properties from objects or arrays
     **/
    private static PropertyAccessor $pa ;

 
    public function __construct()
    {
        self::$pa = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
        
    } 

    private static function getObjectAttr(object|array $object, string|PropertyPathInterface $path): mixed
    {
        return self::$pa->getValue($object, $path);
    }

 
    protected static function getPropertyFromObject(object|array $object, string|PropertyPathInterface $path): mixed
    {
        self::objectOrArrayValidator($object, $path);
        return self::getObjectAttr($object, $path);
    }

    protected static function objectOrArrayValidator(object|array $object, string ...$properties): bool
    {
        foreach ($properties as $property) {

            if (!$property)
                continue;

            if (!self::$pa->isReadable($object, $property))
                if (is_object($object))
                    throw new Exception\InvalidObjectException(sprintf("Property %s is not owned by the object !", $property));
                else
                    throw new Exception\InvalidArrayException(sprintf("Property %s is not owned by the array !", $property));
        }
        return true;
    }


    // protected static function setValue(array &$ar, string $path, mixed $value) {
    //      self::$pa->setValue($ar, $path, $value);
    // }
 

    protected static function  createPropertyPath(PropertyPathInterface|string $path = null):PropertyPathBuilder {
        return new PropertyPathBuilder($path);
   }



   public function getPropertyAccessor(): PropertyAccessor {
        return self::$pa;
   }

}
