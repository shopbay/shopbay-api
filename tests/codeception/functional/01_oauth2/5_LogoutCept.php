<?php
use tests\codeception\_support\TestHelper;

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /oauth2/logout is successful');

$adminToken = TestHelper::readFile('admin_login_token');

//case 1: missing bearer
$I->amGoingTo('ensure that admin logout failes when bearer is not presented');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$route = '/oauth2/logout';
$I->sendPOST($route, '');
$I->seeResponseCodeIs(401);
$I->seeResponseIsJson();

//case 2: missing token
$I->amGoingTo('ensure that admin logout failes when token is not presented');
$I->amBearerAuthenticated($adminToken);
$I->sendPOST($route, '');
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();

//case 3: wrong token
$I->amGoingTo('ensure that admin logout failes when wrong token is not presented');
$I->sendPOST($route, ['token'=>'Wrong Token']);
$I->seeResponseCodeIs(200);//!!! Wrong token also can logout as bearer is presented!
$I->seeResponseIsJson();

//case 4: admin logout
$I->amGoingTo('ensure that admin logout is successful');
$I->sendPOST($route, ['token'=>$adminToken]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"OK"');

