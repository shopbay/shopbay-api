Shopbay API - Open Source Ecommerce Platform  
=======================================

REQUIREMENTS
------------
* shopbay-kernel
* [yii2-oauth2-server](https://github.com/Filsh/yii2-oauth2-server)


Note: 

> The OAuth2 Server library is already bundled in `/modules/oauth2server/vendor/bshaffer`
> (Refer to OAuth2 Server Library for PHP)[https://bshaffer.github.io/oauth2-server-php-docs/]
>
> To run console command, `shopbay-console` is required. 
>

API Reference
-------------
Please refer to [API Reference Guide](/docs/shopbay-api-spec.yaml).


Notes
----
A bug fix is added to this class `OAuth2\ResponseType\AccessToken` for PHP 7.2 compatibility since mcrypt_create_iv function is removed PHP 7.2! use `random_bytes()` instead
