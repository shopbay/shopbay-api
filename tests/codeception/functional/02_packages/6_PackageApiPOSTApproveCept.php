<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/packages/approve';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /packages/approve works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$queryParams = $route.'/2';//Package #2 is created in previous test case

$I->amGoingTo('ensure validation fails when transition object is missing');
$I->sendPOST($queryParams,'');
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();

$I->amGoingTo('ensure approval passes when transition object is present but contains invalid fields');
$I->sendPOST($queryParams,json_encode([
    'reason'=>'ok',
    'wrongField'=>'wrongValue',
]));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();

$I->amGoingTo('ensure approval passes when transition object is missing decision field');
$I->sendPOST($queryParams,json_encode([
    'reason'=>'ok'
]));
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();

$I->amGoingTo('ensure approval passes when transition object is present but wrong value of decision');
$I->sendPOST($queryParams,json_encode([
    'decision'=>'yes',//case sensitive <- correct value should be "Yes"
    'reason'=>'ok'
]));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains('"message":"Invalid process decision"');


$I->amGoingTo('ensure approval passes when transition object is present');
$I->sendPOST($queryParams,json_encode([
    'decision'=>'Yes',
    'reason'=>'ok'
]));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Approved"');
$I->seeResponseContains('"id":2,"name":"Test Package"');
