<?php
use tests\codeception\_support\TestHelper;

$route = '/oauth2/token';
$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /oauth2/token works');

$fo = new TestHelper($I,'oauthClient');
$client = $fo->getData('admin');

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->amHttpAuthenticated($client['client_id'],$client['client_secret']);
$I->sendPOST($route, ['grant_type'=>'client_credentials'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"access_token"');
$I->seeResponseContains('"expires_in"');
$I->seeResponseContains('"token_type"');
$I->seeResponseContains('"scope"');
$postResponse = json_decode($I->grabResponse(),true);
//WRITE Admin token
$fo->writeFile('admin_token',$postResponse['access_token']);

$fo2 = new TestHelper($I,'oauthClient');
$client2 = $fo2->getData('merchant');

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->amHttpAuthenticated($client2['client_id'],$client2['client_secret']);
$I->sendPOST($route, ['grant_type'=>'client_credentials'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"access_token"');
$I->seeResponseContains('"expires_in"');
$I->seeResponseContains('"token_type"');
$I->seeResponseContains('"scope"');
$postResponse = json_decode($I->grabResponse(),true);
//WRITE Merchant token
$fo->writeFile('merchant_token',$postResponse['access_token']);