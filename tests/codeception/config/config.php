<?php
/**
 * Application configuration shared by all test types
 */
return [
    'language' => 'en-US',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/codeception/fixtures',
            'templatePath' => '@tests/codeception/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@tests/codeception/_migrations',
        ],
    ],
    'bootstrap' => ['log'],
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=shopbay_codeception',
            'username' => 'root',
            'password' => 'mysqladmin',
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'log' => [
            'flushInterval' => 3, // <-- input here
            'traceLevel' =>  8,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    //'categories' => ['yii\base\*','CException','application',YII_APP_ID],//to turn on system trace, add "system.*"
                    'levels' => ['error', 'warning','info', 'trace'] ,
                    'maxFileSize'=> 5120,//5M
                ],
            ],
        ],        
    ],
];
