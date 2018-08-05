<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/packages/submit';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /packages/submit works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$queryParams = $route.'/2';//Package #2 is created in previous test case

$I->sendPOST($queryParams,'');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Submitted"');
