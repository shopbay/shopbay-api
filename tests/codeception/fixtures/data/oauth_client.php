<?php
return [
    'system' => [
        'client_id' => 'ShopbayOauthApp',
        'client_secret' => md5(uniqid()),
        'redirect_uri' => 'https://fake/',
        'grant_types' => 'password',
        'user_id' => 0,
        'scope' => null,
    ],
    'admin' => [
        'client_id' => 'adminTestClient',
        'client_secret' => md5(uniqid()),
        'redirect_uri' => 'https://fake/',
        'grant_types' => 'authorization_code password client_credentials',
        'user_id' => 10000,
        'scope' => null,
    ],
    'merchant' => [
        'client_id' => 'merchantTestClient',
        'client_secret' => md5(uniqid()),
        'redirect_uri' => 'https://fake/',
        'grant_types' => 'authorization_code password implicit client_credentials',
        'user_id' => 20000,
        'scope' => null,
    ],
];