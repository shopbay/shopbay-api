<?php
use tests\codeception\_support\TestHelper;

$route = '/v1/packages';

$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that DELETE /packages works');
$accessToken = TestHelper::readFile('admin_token');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->amBearerAuthenticated($accessToken);

$fo = new TestHelper($I,'package');
$package = $fo->getRow('standardPackage');
$id = $package['id'];
$queryParams = $route.'/'.$id;
$I->amGoingTo('ensure delete is successful');
$I->sendDELETE($queryParams);
$I->seeResponseCodeIs(204);
$I->seeInDatabase('s_package', ['status'=>Process::DELETED,'id'=>$id]);

$I->amGoingTo('ensure package is deleted in db and return 404 via GET API');
$I->sendGET($route.'/'.$id);
$I->seeResponseCodeIs(404);
