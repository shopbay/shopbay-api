<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans/submit';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /plans/submit works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'plan');
$plan = $fo->getRow('recurringPlan');
$queryParams = $route.'/'.$plan['id'];

$I->sendPOST($queryParams,'');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Submitted"');
