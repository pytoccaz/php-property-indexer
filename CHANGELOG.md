CHANGELOG
=========

0.1 2022-01-24
-----
 * create `PropertyIndex` class 
 * create tests 


0.2 2022-02-16
-----
 * `PropertyIndex` class extends new `PropertyPicker` class 
 * create new `PropertyTreeBuilder` class 


0.3 2022-02-17
-----
 * tree path edge definitions accept closures


1.0 2022-03-03
-----
PropertyTreeBuilder changes: 
 * rename `PropertyTreeBuilder` class into `PropertyTree` 
 * let leaf Path (valuePath arg) be a Closure
 * change variadic string groupByProperties into list<string|Closure> arg
 * let properties (groupByProperties) path be a Closure
 * add tree and PropertyAccessor getters
 * add mode ARRAY_LEAF to append values to array-typed leaves when key path colision happens


1.1 2022-03-04
-----
PropertyTreeBuilder changes: 
 * groupByProperties props can be any stringable
 * strengthen groupByProperties props control (closure props has to return non null value)