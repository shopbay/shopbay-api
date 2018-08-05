<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_ID') or define('YII_APP_ID', 'api');
require_once(dirname(__FILE__).'/../../common/config/globals.php');
require_once(dirname(__FILE__).'/../config/yii1engine.php');
require_once(readConfig('system','yii2Path').'/vendor/autoload.php');//calls composer autoload.php
//load customized Yii class (enable both Yii 1.x and Yii 2.x)
require(dirname(__FILE__).'/../../../common/components/Yii2.php');
//Start Yii1 engine so that pplication resources that built using Yii1 can be accessed.
bootstrapYii1Engine(YII_APP_ID,true);//true means test env    

//require(__DIR__ . '/../vendor/autoload.php');
//require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../tests/codeception/config/acceptance.php');

(new yii\web\Application($config))->run();
