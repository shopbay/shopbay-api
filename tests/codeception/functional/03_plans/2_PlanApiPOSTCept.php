<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /plans works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'account');

$postFields = [
    'name'=>'Test Basic Plan',
];
$I->amGoingTo('ensure validation fails when type is missing');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();
$I->seeResponseContains('"details":{"type"');

$postFields = array_merge($postFields,[
    'type'=>Plan::FIXED,
    'price'=>8.50,
]);
$I->amGoingTo('ensure validation fails when currency is missing');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();
$I->seeResponseContains('"details":{"currency"');

$postFields = array_merge($postFields,[
    'currency'=>'MYR',
]);
$I->amGoingTo('ensure validation fails when items is missing');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(422);
$I->seeResponseIsJson();
$I->seeResponseContains('"details":{"items"');

$postFields = array_merge($postFields,[
    'items'=>[
        ['name'=>'Plan item a'],
        ['name'=>'Plan item b'],
        ['name'=>'Plan item c'],
    ]
]);
$I->amGoingTo('ensure plan creation pass');
$I->sendPOST($route, json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$postResponse = json_decode($I->grabResponse(),true);
$id = $postResponse['id'];
$fo->log('plan id: '.$id);

$I->amGoingTo('ensure plan creation is inside db and able to GET via API');
$I->sendGET($route.'/'.$id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$getResponse = json_decode($I->grabResponse(),true);
$I->seeResponseContains('"id":'.$id);
$I->seeResponseContains('"currency":"MYR"');
$I->seeResponseContainsJson([
        ['name'=>'Plan item a'],
        ['name'=>'Plan item b'],
        ['name'=>'Plan item c'],
    ]);

$fo->flushLog();
