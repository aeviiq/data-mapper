# Aeviiq PHP Data Mapper

## Why
To provide an easy way to map objects. In the first few releases this is mainly made to map something like a form model, which has optional values and setters, to a read-only version of the object, which could be used in whatever process comes after the form submission. This way the code stays decoupled from the form itself.

## Installation
```
composer require aeviiq/data-mapper
```

## Usage
```php
// The target can be either an object or a string representing the class name of the object you want to map to
DynamicDataMapper::map($source, Foo::class);
DynamicDataMapper::map($source, $foo);
```
