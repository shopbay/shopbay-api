<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/packages';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /packages works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'account');

$postFields = [
    'name'=>'Test Package',
    'type'=>Plan::RECURRING,
];
$I->amGoingTo('ensure validation fails when plans is missing');
$I->sendPOST($route, $postFields);
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();
$I->seeResponseContains('"details":{"plans"');

$postFields = array_merge($postFields,[
    'plans'=>'2,3',//plan id 2 is not in approved status
]);
$I->amGoingTo('ensure package creation fails when one plan is not in approved status');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();
$I->seeResponseContains('not in approved state.');

$postFields = array_merge($postFields,[
    'name'=>'Test Package',
    'plans'=>'3,4',
]);

$I->amGoingTo('ensure package creation pass');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$postResponse = json_decode($I->grabResponse(),true);
$id = $postResponse['id'];
$fo->log('package id: '.$id);

$I->amGoingTo('ensure package creation is inside db and able to GET via API');
$I->sendGET($route.'/'.$id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$getResponse = json_decode($I->grabResponse(),true);
$I->seeResponseContains('"id":'.$id);

$fo->flushLog();
