# Smarter Coffee API for PHP

PHP Smarter Coffee API, inspired by https://github.com/AdenForshaw/smarter-coffee-api

## Usage

Either include and call statically for basic brewing or instantiate:

```php
SmarterCoffee::make('192.168.0.100');
```

```php
$coffeeMaker = new SmarterCoffee('192.168.0.100');
$coffeeMaker->reset();
echo $coffeeMaker->brew();
```
