<?php
return [
    'TestShop.1' => [
        'id' => 20000,
        'account_id' => 20000,
        'shop_id' => 20000,
        'subscription_no' => 'test_sub',
        'package_id' => 1,
        'plan_id' => 3,
        'start_date' => Helper::getMySqlDateFormat(time()),
        'end_date' => '9999-06-01',
        'status' => Process::SUBSCRIPTION_ACTIVE,
        'create_time' => time(),
        'update_time' => time(),
    ],
];