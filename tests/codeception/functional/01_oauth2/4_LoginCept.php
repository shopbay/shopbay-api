<?php
use tests\codeception\_support\TestHelper;

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /oauth2/admin/login is successful');
$I->haveHttpHeader('Content-Type', 'application/json');

$fo = new TestHelper($I,'account');
$user = $fo->getData('admin');

$route = '/oauth2/admin/login';

//case 1: correct login by username
$I->amGoingTo('ensure that admin login is successful');
$I->amHttpAuthenticated($user['name'],'adminPassword');
$I->sendPOST($route, '');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"access_token"');
$postResponse = json_decode($I->grabResponse(),true);
//WRITE Admin token
$fo->writeFile('admin_login_token',$postResponse['access_token']);

//case 2: wrong username
$I->amGoingTo('ensure that login by wrong username will fail');
$I->amHttpAuthenticated('Wrong username','adminPassword');
$I->sendPOST($route, '');
$I->seeResponseCodeIs(401);
$I->seeResponseIsJson();

//case 3: wrong password
$I->amGoingTo('ensure that login by wrong password will fail');
$I->amHttpAuthenticated($user['name'],'Wrong Password');
$I->sendPOST($route, '');
$I->seeResponseCodeIs(401);
$I->seeResponseIsJson();

//case 4: correct login by email
$I->amGoingTo('ensure that login by email is successful');
$I->amHttpAuthenticated($user['email'],'adminPassword');
$I->sendPOST($route, '');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"access_token"');

//case 5: correct login for merchant
$merchant = $fo->getData('merchant');
//case 5: correct login by username
$I->amGoingTo('ensure that normal user login is successful');
$I->amHttpAuthenticated($merchant['name'],'merchantPassword');
$I->sendPOST('/oauth2/merchant/login', '');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"access_token"');
$postResponse = json_decode($I->grabResponse(),true);
//WRITE Admin token
$fo->writeFile('merchant_login_token',$postResponse['access_token']);

//case 6: normal user but use admin/login url, expects to fail
$I->amGoingTo('ensure that normal user login using url oauth2/admin/login will fail');
$I->sendPOST('/oauth2/admin/login', '');
$I->seeResponseCodeIs(401);
$I->seeResponseIsJson();
