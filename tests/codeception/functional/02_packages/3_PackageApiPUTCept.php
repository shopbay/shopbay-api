<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/packages';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that PUT /packages works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'package');
$package = $fo->getRow('standardPackage');
$queryParams = $route.'/'.$package['id'];
$postFields = [
    'plans'=>'3,4',//add plan #4
];
$I->amGoingTo('ensure update pass');
$I->sendPUT($queryParams, json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(
        ['name'=>'Standard Package']
    );

$postFields = array_merge($postFields,[
    'wrongField'=>'wrongValue',
]);
$I->amGoingTo('ensure validation fails when invalid field is updated');
$I->sendPUT($queryParams, json_encode($postFields));
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
