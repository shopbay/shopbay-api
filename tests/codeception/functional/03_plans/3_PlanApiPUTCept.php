<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that PUT /plans works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'plan');
$plan = $fo->getRow('fixedPlan');
$queryParams = $route.'/'.$plan['id'];
$postFields = [
    'name'=>'Basic Fixed Modified',
];
$I->amGoingTo('ensure update pass');
$I->sendPUT($queryParams, json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(
        ['name'=>'Basic Fixed Modified']
    );

$postFields = array_merge($postFields,[
    'wrongField'=>'wrongValue',
]);
$I->amGoingTo('ensure validation fails when invalid field is updated');
$I->sendPUT($queryParams, json_encode($postFields));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
