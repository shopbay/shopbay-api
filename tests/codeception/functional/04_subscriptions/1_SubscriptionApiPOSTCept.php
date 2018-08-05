<?php
use tests\codeception\_support\TestHelper;

$testPlanName = '';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /subscriptions works for permission check');

//CREATE A NEW PLAN
$I->amGoingTo('ensure a new plan is created and ready for test');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);
$postFields = [
    'name'=>'Test_Plan_for_Subscription_Check',
    'type'=>Plan::RECURRING,
    'recurring'=>Plan::MONTHLY,
    'price'=>8.50,
    'currency'=>'MYR',
    'items'=>[
        ['name'=> Feature::getKey(Feature::$hasShopLimitTierN)],
        ['name'=> Feature::getKey(Feature::$hasProductLimitTier1)],
    ]
];
$I->sendPOST('/v1/plans', json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$postResponse = json_decode($I->grabResponse(),true);
$planId = $postResponse['id'];

//SUBMIT PLAN
$I->amGoingTo('ensure that plan submission is working');
$I->sendPOST('/v1/plans/submit/'.$planId,'');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Submitted"');

//APPROVE PLAN
$I->amGoingTo('ensure that plan approval is working');
$I->sendPOST('/v1/plans/approve/'.$planId,json_encode([
    'decision'=>'Yes',
    'reason'=>'ok'
]));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Approved"');

//CREATE A NEW PACKAGE
$I->amGoingTo('ensure a new package is created and ready for test');
$postFields = [
    'name'=>'My Test Package',
    'type'=>Plan::RECURRING,
    'plans'=>'4,'.$planId,//need to add plan_id 4 as Package requries both yearly and monthly
];
$I->sendPOST('/v1/packages', json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$postResponse = json_decode($I->grabResponse(),true);
$packageId = $postResponse['id'];

//SUBMIT PACKAGE
$I->amGoingTo('ensure that package submission is working');
$I->sendPOST('/v1/packages/submit/'.$packageId,'');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Submitted"');

//APPROVE PACKAGE
$I->amGoingTo('ensure that package approval is working');
$I->sendPOST('/v1/packages/approve/'.$packageId,json_encode([
    'decision'=>'Yes',
    'reason'=>'ok'
]));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Approved"');


//SUBSCRIBE PLAN AS MERCHANT
$I->amGoingTo('ensure that plan subscription is working (going into Pending)');
$merchantAccessToken = TestHelper::readFile('merchant_token');
$I->amBearerAuthenticated($merchantAccessToken);
$postFields = [
    'shop'=>20000,//shop id for Merchant 1
    'package'=>$packageId,
    'paymentNonce'=>'Dummy Nonce',
//    'paymentToken'=>'Dummy Token',
];
$I->sendPOST('/v1/plans/subscribe/'.$planId,json_encode($postFields));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"status":"Pending"');

$fo = new TestHelper($I,'account');
$user = $fo->getRow('merchant');
$I->seeInDatabase('s_subscription', ['account_id'=>$user['id'],'plan_id'=>$planId,'start_date'=>Helper::getMySqlDateFormat(time())]);
//Note: s_rbac_assignment record not created yet until subscription went active! via webhook or command)
//$I->seeInDatabase('s_rbac_assignment', ['user_id'=>$user['id'],'item_name'=>'Test_Plan_for_Subscription_Check']);
$I->dontSeeInDatabase('s_rbac_assignment', ['user_id'=>$user['id'],'item_name'=>'Test_Plan_for_Subscription_Check']);

//CHECK MERCHANT HAS THE SUBSCRIPTION 1
$I->amGoingTo('ensure that after subscription and merchant owns it');
$I->amBearerAuthenticated($merchantAccessToken);
$checkPostFields = [];
$I->sendPOST('/v1/subscriptions/check/'.Feature::patternize(Feature::$hasShopLimitTierN),json_encode($checkPostFields));
$I->seeResponseCodeIs(401);//Note: as status in Pending; no record return
$I->seeResponseIsJson();

//second time subscribed, expect error
//$I->sendPOST('/v1/plans/subscribe/'.$planId,json_encode($postFields));
//$I->seeResponseCodeIs(422);
//$I->seeResponseIsJson();
//$I->seeResponseContains('"You have already subscribed to this plan."');
