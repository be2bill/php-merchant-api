THIS REPOSITORY HAS BEEN MOVED TO https://github.com/dalenys/php-merchant-api/ it won't receive any update

NOTE: the namespace has been moved from Be2bill to Dalenys.

[Be2bill Merchant API] (http://developer.be2bill.com/)

A simple PHP implementation of the Be2bill payment platform API.

[![Build Status](https://travis-ci.org/be2bill/php-merchant-api.svg?branch=master)](https://travis-ci.org/be2bill/php-merchant-api)
[![Latest Stable Version](https://poser.pugx.org/be2bill/php-merchant-api/v/stable)](https://packagist.org/packages/be2bill/php-merchant-api) 
[![Total Downloads](https://poser.pugx.org/be2bill/php-merchant-api/downloads)](https://packagist.org/packages/be2bill/php-merchant-api) 
[![License](https://poser.pugx.org/be2bill/php-merchant-api/license)](https://packagist.org/packages/be2bill/php-merchant-api)

This library closely adheres to the official [Merchant API guidelines](https://github.com/be2bill/merchant-api-guidelines).

You can read the API [apiGen generated documentation](https://codedoc.pub/be2bill/php-merchant-api/master/index.html)  


## Installing

### Composer
You can easily install this library by adding the following lines to your composer.json file

```json
{
  "require": {
    "be2bill/php-merchant-api": "1.*"
  }
}
```

or by using this command line in a terminal at the root of your project

```bash
composer require be2bill/php-merchant-api 1.*
```

### Manual install
You can install this library manually by simply cloning it to your project and including scripts/autoload.php


## Using
 
### Building a simple payment form

Here is the code sample for implementing a simple 10€ payment form

```php
<?php

define('BE2BILL_IDENTIFIER', 'YOUR ACCOUNT IDENTIFIER');
define('BE2BILL_PASSWORD', 'YOUR ACCOUNT PASSWORD');

// Just implement BE2BILL_IDENTIFIER and BE2BILL_PASSWORD as defined
$be2bill = Be2bill_Api_ClientBuilder::buildProductionFormClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

echo $be2bill->buildPaymentFormButton(10000, 'order_123', 'user_123456', 'Payment sample');
```

### Payment options
You can specify some additional options to the buildPaymentFormButton method.
The most useful options are:
- CREATEALIAS = yes/no => Ask for the creation of a rebilling alias (allowing one click payments or subscription like payments)
- 3DSECURE = yes/no => Ask for 3DSECURE authentication
- CARDFULLNAME => When set the card holder inputs will be filled with specified data

For the full list of options you can read the Be2bill documentation

### Sandbox environment
You can easily test your integration with the sandbox environment. This environment will simulate payments without processing any real money move.
You just have to use another builder method:

```php
<?php

$be2bill = Be2bill_Api_ClientBuilder::buildSandboxFormClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);
```

### Transaction edition
You can edit a transaction: capturing or refunding an authorization.
You should use the direct link AP:

```php
<?php

$be2bill = Be2bill_Api_ClientBuilder::buildSandboxDirectLinkClient(BE2BILL_IDENTIFIER, BE2BILL_PASSWORD);

$be2bill->capture('A1234', 'order_42', 'capturing a transaction');
```

## Testing

First you have to copy tests/ftests/config.php.dist to tests/ftests/config.php

If you want to run the unit test suite you can run from the project root:

```shell
phpunit tests/utests
```

If you want to run the functional test suite (really send dummy payment requests to the Be2bill sandbox): edit the config.php and replace IDENTIFIER and PASSWORD with the provided one (sandbox)

```shell
phpunit tests/ftests
```

If you want to run all the tests, configure the functional test then simply:

```shell
phpunit
```
