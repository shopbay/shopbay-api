<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

use yii\rest\Controller;
/**
 * Handle a request for an OAuth2.0 Access Token and send the response to the client
 *
 * @author kwlok
 */
class TokenController extends Controller 
{ 
    /**
     * Handles access token generation based on grant_type
     */
    public function actionIndex()
    {
        $this->module->server->handleTokenRequest($this->module->request)->send();
    }
//    /**
//     * Useful and handy method for further logic processing
//     */
//    protected function getUser()
//    {
//        $tokenData = $this->module->server->getAccessTokenData($this->module->request,$this->module->response);
//        return $tokenData['user_id'];
//    }
}
