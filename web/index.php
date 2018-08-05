<?php
defined('APP_CONFIG') or define('APP_CONFIG',dirname(__FILE__).'/../config.json');
$appName = basename(dirname(dirname(__FILE__)));// The app directory name, e.g. shopbay-api
defined('YII_APP_ID') or define('YII_APP_ID', $appName);
require_once(dirname(__FILE__).'/../../shopbay-kernel/config/globals.php');
require_once(dirname(__FILE__).'/../config/yii1engine.php');
//load customized Yii class (enable both Yii 1.x and Yii 2.x)
require(dirname(__FILE__).'/../../shopbay-kernel/components/Yii2.php');

//Start Yii1 engine so that application resources that built using Yii1 can be accessed.
bootstrapYii1Engine(YII_APP_ID);
//Starts Yii2 Application
$config = require(__DIR__ . '/../config/web.php');
$application = new yii\web\Application($config);
$application->run();
