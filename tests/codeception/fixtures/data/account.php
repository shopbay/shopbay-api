<?php
return [
    'admin' => [
        'id' => 10000,
        'name' => 'adminTester',
        'password' => Yii::$app->getSecurity()->generatePasswordHash('adminPassword'),
        'email' => 'admin@shopbay.localhost',
        'status' => Process::ACTIVE,
        'create_time' => time(),
        'update_time' => time(),
    ],
    'merchant' => [
        'id' => 20000,
        'name' => 'merchantTester',
        'password' => Yii::$app->getSecurity()->generatePasswordHash('merchantPassword'),
        'email' => 'merchant@shopbay.localhost',
        'reg_ip' => '127.0.0.1',
        'activate_str' => Role::ACTIVATED,
        'activate_time' => time(),
        'status' => Process::ACTIVE,
        'create_time' => time(),
        'update_time' => time(),
    ],    
];