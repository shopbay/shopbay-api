<?php
return [
    'standardPackage' => [
        'id' => 1,
        'account_id' => 10000,
        'name' => 'Standard Package',
        'type' => Plan::RECURRING,
        'plans' => '3',
        'status' => Process::PACKAGE_DRAFT,
        'create_time' => time(),
        'update_time' => time(),
    ],
];