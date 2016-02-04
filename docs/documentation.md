#Lebran Container


Simple dependency injection container for PHP.

## Installation

Require in your project with [composer](https://getcomposer.org/download/) :

```bash
$ composer require lebran/container
```

##Usage

Creating a container is a matter of creating a ``\Lebran\Container`` instance:

```php
$di = new \Lebran\Container();
```

###Defining Services
A service is an object that does something as part of a larger system. Examples
of services: a database connection, a templating engine, or a mailer. Almost
any **global** object can be a service. Lebran Container supports 3 types of definition:
**string**, **object** and **anonymous function**. Too supports 2 mode: **shared** and **not shared**.
```php
$di->set(<service name>, <definition>, <mode>);
```

Services are defined by **string** that return an instance of an
object:

``` php
$di->set('some_service', '\MyNamespace\MyService');
```
Service defined by **object** returns object:

```php 
$di->set('some_service', new \MyNamespace\MyService());
```

But if you **no** choose shared mode, container returns **clone** of object

```php
$di->get('some_service'); // clone 

$di->setShared('some_service');

$di->get('some_service'); // singleton
```

