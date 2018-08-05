<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans/approve';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /plans/approve works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'plan');
$plan = $fo->getRow('recurringPlan');
$queryParams = $route.'/'.$plan['id'];

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
$I->seeResponseContains('"items"');
$I->seeInDatabase('s_rbac_item', ['name'=>$plan['name'],'type'=>1]);
$I->seeInDatabase('s_rbac_rule', ['name'=>'SubscriptionRule']);
$response = json_decode($I->grabResponse(),true);
foreach ($response['items'] as $item) {
    $I->seeInDatabase('s_rbac_item', ['name'=>$item['name'],'type'=>2,'rule_name'=>'SubscriptionRule']);
    $I->seeInDatabase('s_rbac_item_child', ['child'=>$item['name'],'parent'=>$plan['name']]);
}
