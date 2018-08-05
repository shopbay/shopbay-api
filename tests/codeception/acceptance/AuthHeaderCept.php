<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure that authentication fails for wrong access token');

$I->haveHttpHeader('Content-Type', 'application/json');
$I->amHttpAuthenticated('dummy access token','');
$I->sendGET('/plans');
$I->seeResponseCodeIs(401);
$I->seeResponseIsJson();
 