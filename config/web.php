<?php
$params = require(__DIR__ . '/params.php');
$config = [
    'id' => YII_APP_ID,
    'basePath' => dirname(__DIR__),
    'vendorPath' => readConfig('system','yii2Path').'/vendor',
    'timezone' => APP_TIMEZONE,//refer to globals.php
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => '\app\modules\v1\Module',  
            'basePath' => '@app/modules/v1',
        ],
        'oauth2' => [
            'class' => 'app\modules\oauth2server\Module',
//            'class' => \filsh\yii2\oauth2server\Module::class,
            'components' => [ 
                'request' => function () { 
                    return \filsh\yii2\oauth2server\Request::createFromGlobals();
                },
                'response' => [
                    'class' => \filsh\yii2\oauth2server\Response::class,
                ],
            ],
            'tokenAccessLifetime' => 3600 * 8,//8 hours 
            'storageMap' => [
                'user_credentials' => 'app\components\ApiUserCredentials'
            ],
            'grantTypes' => [
                //'authorization_code' => [
                //    'class' => 'OAuth2\GrantType\AuthorizationCode',
                //],
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ],
                'client_credentials' => [
                    'class' => 'OAuth2\GrantType\ClientCredentials',
                    'allow_credentials_in_request_body'=>false,
                ],
            ]
        ],        
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable'=>'s_rbac_item',
            'itemChildTable'=>'s_rbac_item_child',
            'ruleTable'=>'s_rbac_rule',
            'assignmentTable'=>'s_rbac_assignment',
        ],        
        'request' => [
            'parsers' => [
               'application/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation'=>false,
            'enableCookieValidation' => false,
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'INPUT_ANY_VALUE',//input any value
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            /**
             * For Yii1 app user, refer to api.components.ApiUser
             * @see bootstrapYii1Engine() for its loading
             */
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            //'logger' => Yii::createObject('yii\log\Logger'),<--if set to this, log file will not be generated
            'flushInterval' => 3, // <-- input here
            'traceLevel' => 8 ,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['yii\base\*','CException','application',YII_APP_ID],//to turn on system trace, add "system.*"
                    'levels' => YII_DEBUG ? ['error', 'warning','info', 'trace'] : ['info' ,'error', 'warning'] ,
                    'maxFileSize'=> 5120,//5M
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/shop',
                        'v1/product',
                        'v1/subscription',
                        'v1/plan',
                        'v1/package',
                    ],
                    'extraPatterns' => [
                        'GET published' => 'published',
                        'POST apply/' => 'apply',
                        'POST submit/{id}' => 'submit',
                        'POST approve/{id}' => 'approve',
                        'POST subscribe/{id}' => 'subscribe',
                        'POST subscribe/{id}/{subscriber}' => 'subscribe',
                        'DELETE unsubscribe/{id}' => 'unsubscribe',
                        'DELETE unsubscribe/{id}/{subscriber}' => 'unsubscribe',
                        'POST check/{subscription}' => 'check',
                    ],
                    'tokens' => [
                        '{id}' => '<id:\\w+>',
                        '{subscription}' => '<subscription:\\w+>',
                        '{subscriber}' => '<subscriber:\\w+>',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['oauth2'],
                    'extraPatterns' => [
                        'POST authorize' => 'authorize',//to get authorization code
                        //'POST authorize' => 'authorize',//used by authorize/example action 
                        'POST token' => 'token',
                        //'POST login' => 'login',//todo disable generic login without role check (this will be used later for shopbay marketplace
                        'POST customer/login' => 'customer',//login for customer app
                        'POST merchant/login' => 'merchant',//login for merchant app
                        'POST admin/login' => 'admin',//login for admin app
                        'POST customer/activate' => 'customer/activate',//login for customer app
                        'POST merchant/activate' => 'merchant/activate',//login for merchant app
                        'POST admin/activate' => 'admin/activate',//login for admin app
                        'POST logout' => 'logout',
                    ],
                    'pluralize' => false,
                ],                 
                //'/' => 'site/index',
                //'<action:\w+>' => 'site/<action>',
                //'<controller:\w+>/<id:\d+>' => '<controller>/view',
                //'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                //'<controller:\w+>/<action:\w+>' => '<controller>/<action>',                
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => readConfig('elasticsearch','host').':'.readConfig('elasticsearch','port')],
                // configure more hosts if you have a cluster
            ],
        ],                
    ],   
    'params' => $params,
];

return $config;
