<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use app\modules\oauth2server\filters\auth\ResourceAuth;
/**
 * Description of LogoutController
 * 
 * This requires a valid access token to logout, and when logout, token will be revoked.
 *
 * @author kwlok
 */
class LogoutController extends Controller 
{ 
    /**
     * Serializer definition
     * @var type 
     */
    public $serializer = [
        'class' => 'app\modules\v1\components\Serializer',
    ];   
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => ResourceAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }   
    /**
     * Handles logout
     * [1] First revoke token
     * [2] Secondly do a service logout (to have activitiy recorded)
     */
    public function actionIndex()
    {
        $this->module->server->getTokenController()->revokeToken($this->module->request,$this->module->response);
        \Yii::app()->getModule('accounts')->serviceManager->logout();
        return ['status'=>'OK'];
    }    
}
