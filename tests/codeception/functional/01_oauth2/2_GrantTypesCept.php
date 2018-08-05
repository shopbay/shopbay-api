<?php
use tests\codeception\_support\TestHelper;

$route = '/oauth2/token';
$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /oauth2/token Grant_Types is validated');

$fo = new TestHelper($I,'oauthClient');
$client = $fo->getData('admin');

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->amHttpAuthenticated($client['client_id'],$client['client_secret']);
//case 1: unsupported grant type
$I->sendPOST($route, ['grant_type'=>'wrong_grant_type'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"unsupported_grant_type"');

//case 2: unassigned grant type 
$I->sendPOST($route, ['grant_type'=>'implicit'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"unsupported_grant_type"');

