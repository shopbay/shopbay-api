<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\filters\auth;

use Yii;
use yii\web\UnauthorizedHttpException;
/**
 * Description of MerchantController
 *
 * @author kwlok
 */
class ResourceAuth extends \yii\filters\auth\CompositeAuth
{
    public $module = 'oauth2';
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $module = Yii::$app->getModule($this->module);
        // Handle a request to a resource and authenticate the access token
        $value = $module->server->verifyResourceRequest($module->request,$module->response);
        if (!$module->response->isSuccessful()){
            logErrorDump(__METHOD__. ' response ',$module->response);
            throw new UnauthorizedHttpException($module->response->getStatusText().': '.$module->response->getParameter('error_description'));
        }        
    
        return parent::beforeAction($action);
    }
}