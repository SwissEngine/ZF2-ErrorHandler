SwissEngine\ErrorHandler
===============

This library aims at providing a simple way to handle errors in all of your ZF2 application. When an error is triggered, whether it is in your controller, your libraries or anywhere in your views, an exception is thrown hence preserving your application from unmonitored errors.

Installation
------------

Suggested installation method is through [composer](http://getcomposer.org/):

```php
php composer.phar require swissengine/zf2-errorhandler:dev-master
```

Setup
-----

If you use Zend Framework 2, you can now enable this module in your application by adding it to `config/application.config.php` as `SwissEngine\ErrorHandler`.

