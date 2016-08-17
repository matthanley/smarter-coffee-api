# Smarter Coffee API for PHP

PHP Smarter Coffee API, inspired by https://github.com/AdenForshaw/smarter-coffee-api

## Usage

```php
$coffeeMaker = new SmarterCoffee('192.168.0.100');
$coffeeMaker->reset();
$coffeeMaker->setCups(4);
$coffeeMaker->setStrength(1);
$coffeeMaker->setGrind(true);
$coffeeMaker->brew();
```
