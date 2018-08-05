<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/plans';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that DELETE /plans works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'plan');
$plan = $fo->getRow('fixedPlan');
$id = $plan['id'];
$queryParams = $route.'/'.$id;
$I->amGoingTo('ensure delete is successful');
$I->sendDELETE($queryParams);
$I->seeResponseCodeIs(204);
$I->seeInDatabase('s_plan', ['status'=>Process::DELETED,'id'=>$id]);

$I->amGoingTo('ensure plan is deleted in db and return 404 via GET API');
$I->sendGET($route.'/'.$id);
$I->seeResponseCodeIs(404);
