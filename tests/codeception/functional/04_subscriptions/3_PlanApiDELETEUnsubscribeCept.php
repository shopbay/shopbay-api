<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans/unsubscribe';

$subscribedPlan = unserialize(TestHelper::readFile('subscribed_plan'));

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /plans/unsubscribe works');

$accessToken = TestHelper::readFile('merchant_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'account');
$user = $fo->getRow('merchant');

$queryParams = $route.'/'.$subscribedPlan['id'];
$I->sendDELETE($queryParams,'');
$I->seeResponseCodeIs(404);//Plan is in Pending, hence error code 404
$I->seeResponseIsJson();
$I->seeInDatabase('s_subscription', ['account_id'=>$user['id'],'plan_id'=>$subscribedPlan['id'],'status'=>Process::SUBSCRIPTION_PENDING]);
$I->dontSeeInDatabase('s_rbac_assignment', ['user_id'=>$user['id'],'item_name'=>$subscribedPlan['name']]);
