<?php
return [
    'adminTester.1' => [
        'itemname' => Role::ADMINISTRATOR,
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'adminTester.2' => [
        'itemname' => Role::ACTIVATED,
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'adminTester.3' => [
        'itemname' => Role::PACKAGES_CREATOR,//need this role to get package listing
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'adminTester.4' => [
        'itemname' => Role::PACKAGES_APPROVER,//need this role to get package approval
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'adminTester.5' => [
        'itemname' => Role::PLANS_CREATOR,//need this role to get plan listing
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'adminTester.6' => [
        'itemname' => Role::PLANS_APPROVER,//need this role to get plan approval
        'userid' => 10000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'merchantTester.1' => [
        'itemname' => Role::MERCHANT,
        'userid' => 20000,
        'bizrule' => null,
        'data' => 'N;',
    ],
    'merchantTester.2' => [
        'itemname' => Role::ACTIVATED,
        'userid' => 20000,
        'bizrule' => null,
        'data' => 'N;',
    ],
];