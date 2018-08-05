<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/subscriptions';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that GET /subscriptions works');

$accessToken = TestHelper::readFile('merchant_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$I->sendGET($route);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"items"');
$I->seeResponseContains('"_meta"');
$I->seeResponseContains('"_links"');
$I->seeResponseContains('"totalCount"');
$I->seeResponseContainsJson(['totalCount'=>1]);
$getResponse = json_decode($I->grabResponse(),true);
$subscription = $getResponse['items'][0];//take first one to test unsubscribe


$I->amGoingTo('ensure subscription is inside db and able to GET via API');
$I->sendGET($route.'/'.$subscription['id']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"id":'.$subscription['id']);

//WRITE newly subscribed plan into file
$fo = new TestHelper($I,'account');
$fo->writeFile('subscribed_plan',serialize($subscription['plan']));