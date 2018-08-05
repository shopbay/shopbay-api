<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PATH));
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(dirname(__DIR__)) . '/web/index-test.php');

//initApiEnvironment
defined('APP_CONFIG') or define('APP_CONFIG',dirname(__FILE__).'/../../config.json');
$appName = basename(dirname(dirname(dirname(__FILE__))));// The app directory name, e.g. shopbay-api
defined('YII_APP_ID') or define('YII_APP_ID', $appName);
require_once(dirname(__FILE__).'/../../../shopbay-kernel/config/globals.php');
require_once(dirname(__FILE__).'/../../config/yii1engine.php');
require_once(readConfig('system','yii2Path').'/vendor/autoload.php');
//load customized Yii class (enable both Yii 1.x and Yii 2.x)
require(dirname(__FILE__).'/../../../shopbay-kernel/components/Yii2.php');
bootstrapYii1Engine(YII_APP_ID,true);

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_HOST);
$_SERVER['SERVER_PORT'] =  parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PORT) ?: '80';

Yii::setAlias('@tests', dirname(__DIR__));
