<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

use Sii;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
/**
 * Description of AuthorizeController
 * <pre>
 * //Example invocation:
 * http://api.shopbay.org/oauth2/authorize?response_type=code&client_id=<client_id>&state=<state_value>&redirect_uri=<redirect_uri>
 * 
 * </pre>
 * @author kwlok
 */
class AuthorizeController  extends Controller 
{
    /**
     * Customized action to get authorization_code;
     * Required when client is enabled with grant_types: authorization_code
     * HTTP GET param: client_id, client_secret, and user_id must be passed in to get authorization_code
     * http://api.shopbay.org/oauth2/authorize?response_type=code&client_id=<client_id>&client_secret=<client_secret>&user_id=<user_id>&state=<state_value>&redirect_uri=<redirect_uri>
     * 
     * @return mixed
     */
    public function actionIndex()
    {
        $module = Yii::$app->getModule('oauth2');
        
        // validate the authorize request
        if (!$module->server->validateAuthorizeRequest($module->request, $module->response)) {
            $module->response->send();
            die;
        }
        
        //find oauth client to get authorization code
        $client = \filsh\yii2\oauth2server\models\OauthClients::findOne(['client_id'=>$_GET['client_id'],'client_secret'=>$_GET['client_secret'],'grant_types'=>'authorization_code']);
        if ($client!=null){
            $is_authorized = true;
            $user_id = isset($_GET['user_id'])?$_GET['user_id']:null;
            $module->server->handleAuthorizeRequest($module->request, $module->response, $is_authorized, $user_id);
            $code = substr($module->response->getHttpHeader('Location'), strpos($module->response->getHttpHeader('Location'), 'code=')+5, 40);
            // this is only to return authorization code and not doing uri redirect
            $module->response->setParameters([
                'authorization_code'=>$code,
                'redirect_uri'=>isset($_GET['redirect_uri'])?$_GET['redirect_uri']:'',
            ]);
            $module->response->send();        
        }
        else
            throw new NotFoundHttpException(Sii::t('sii','Client not found.'));
    }          
    /**
     * Required when enable grant_types: authorization_code
     * Example codes to get authorization code
     * @return mixed
     */
//    public function actionExample()
//    {
//        if (Yii::$app->getUser()->getIsGuest())
//           return $this->redirect('login');
//        $module = Yii::$app->getModule('oauth2');
//        
//        // validate the authorize request
//        if (!$module->server->validateAuthorizeRequest($module->request, $module->response)) {
//            $module->response->send();
//            die;
//        }
//        
//        // display an authorization form
//        if (empty($_POST)) {
//            exit('<form method="post">
//              <label>Do You Authorize TestClient?</label><br />
//              <input type="submit" name="authorized" value="yes">
//              <input type="submit" name="authorized" value="no">
//            </form>');
//        }
//        // print the authorization code if the user has authorized your client
//        $is_authorized = ($_POST['authorized'] === 'yes');
//        $user_id = 0;//sample value
//        $module->server->handleAuthorizeRequest($module->request, $module->response, $is_authorized, $user_id);
//        if ($is_authorized) {
//          // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
//          $code = substr($module->response->getHttpHeader('Location'), strpos($module->response->getHttpHeader('Location'), 'code=')+5, 40);
//          exit("SUCCESS! Authorization Code: $code");
//        }
//        $module->response->send();        
//
//    }       
}
