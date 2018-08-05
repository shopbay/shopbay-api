<?php
use tests\codeception\_support\TestHelper;

$route = '/oauth2/token';
$I = new FunctionalTester($scenario);
$I->wantTo('Ensure that POST /oauth2/token Authorization Header is validated');

$fo = new TestHelper($I,'oauthClient');
$client = $fo->getData('admin');

//case 1: wrong content type
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST($route, ['grant_type'=>'client_credentials'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"invalid_request"');

//case 1: missing Auth header
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST($route, ['grant_type'=>'client_credentials'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"invalid_client"');

//case 2: wrong client secret
$I->amHttpAuthenticated($client['client_id'],'wrong secret');
$I->sendPOST($route, ['grant_type'=>'client_credentials'] );
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('"invalid_client"');
