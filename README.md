# Lebran Container

Simple dependency injection container


[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lebran/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lebran/container/?branch=master)
[![Total Downloads](https://poser.pugx.org/lebran/container/downloads)](https://packagist.org/packages/lebran/container) 
[![Latest Stable Version](https://poser.pugx.org/lebran/container/v/stable)](https://packagist.org/packages/lebran/container) 
[![Latest Unstable Version](https://poser.pugx.org/lebran/container/v/unstable)](https://packagist.org/packages/lebran/container) [![License](https://poser.pugx.org/lebran/container/license)](https://packagist.org/packages/lebran/container)


## Installation

Require in your project with [composer](https://getcomposer.org/download/) :

```bash
$ composer require lebran/container
```

## Example 

In your bootstrap file `index.php` :

```php
<?php

// Include autoloader
include __DIR__."/vendor/autoload.php";

// Create service container
$di = new \Lebran\Container();

// Container supports 3 types of definition

// Type 1: Object
$di->set('myservice', new \MyNamespace\MyService());

// Type 2: String
$di->set('myservice2', '\MyNamespace\MyService2');

// Type 3: Closure
$di->set('myservice3', 
    function(){
        return new \MyNamespace\MyService3();
    }
);

// Getting service 
$di->get('myservice');

```

Extended example coming soon ...
