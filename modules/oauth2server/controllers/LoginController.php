<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

use yii\rest\Controller;
/**
 * Description of LoginController
 * 
 * This controller will first try to authenticate user based on HTTP Basic Auth
 * and, if successful, it will internally getting access token and return back to user
 *
 * @author kwlok
 */
class LoginController extends Controller 
{ 
    protected $uname, $uid, $pwd;
    protected $identityClass = '\IdentityUser';
    protected $activateToken;//the activation token
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
                'class' => \yii\filters\auth\HttpBasicAuth::className(),
                'auth' => [$this,'authUser']
            ],
        ]);
    }  
    /**
     * Authenticate user
     * @param type $username
     * @param type $password
     * @return type
     */
    public function authUser($username,$password)
    {
        try {
            $this->findActivationToken($_SERVER['REQUEST_URI']);
            $this->uname = $username;
            $this->pwd = $password;
            $user = \app\models\User::findUser($this->uname,$this->pwd,$this->identityClass,$this->activateToken);  
            $this->uid = $user->id;
            return $user;
            
        } catch (\CException $ex) {
            logError(__METHOD__.' error',$ex->getMessage(),false);
            $this->disableLoggingGlobalPHPVars();
            return null;
        }
    }
    /**
     * Handles login based grant_type 'password' with valid client id/secret
     * If successful, return access token 
     */
    public function actionIndex()
    {
        $this->contructTokenRequest();
        $response = $this->module->server->handleTokenRequest(\OAuth2\Request::createFromGlobals());
        logInfo(__METHOD__.' login status',$response);
        $params = $response->getParameters();
        if (isset($params['error']))
            return $params;
        else
            return $this->returnUserData($params);
    }
    /**
     * Handles activation login based grant_type 'password' with valid client id/secret
     * If successful, return access token 
     * @todo should pass in Activation token and validate! else anybody with this url may still have access
     */
    public function actionActivate()
    {
        return $this->runAction('index');//same logic; use action name to differentiate the request
    }
    /**
     * Set proper request params suitable to get access token
     * @param type $app
     */
    protected function contructTokenRequest()
    {
        unset($_POST);
        $grant_type = 'password';
        //use System client app to get token
        $app = \filsh\yii2\oauth2server\models\OauthClients::findOne(['user_id'=>\Account::SYSTEM,'client_id'=>param('OAUTH_CLIENT_PORTAL')]);
        $_SERVER['PHP_AUTH_USER']= $app->client_id;
        $_SERVER['PHP_AUTH_PW']= $app->client_secret;
        $_SERVER['CONTENT_TYPE']= 'text/plain;charset=UTF-8';//MUST set to this content type to work!
        $_POST['grant_type']= $grant_type;
        $this->setUserCredentials();
        logTrace(__METHOD__.' grant_type '.$grant_type.' ok');
        $this->disableLoggingGlobalPHPVars();
    }
    /**
     * Set user credentials to request for token
     */
    protected function setUserCredentials()
    {
        $_POST['username'] = $this->uname;
        $_POST['password'] = $this->pwd;
    }
    /**
     * Return user data after login successfully
     * @param type $params
     * @return type
     */
    protected function returnUserData($params)
    {
        return array_merge($params,[
            'user_id'=>$this->uid,
        ]);
    }
    /**
     * Disable logging targets context information to each batch of log messages. 
     * This is for security reason as not to print user sensitive info in log file
     * @param array $logVars e.g. ['_SERVER','_POST]; By default, the values of these global PHP variable    
     * 
     */
    protected function disableLoggingGlobalPHPVars($logVars=[])
    {
        foreach (\Yii::$app->log->targets as $target){
            $target->logVars = $logVars;
        }
    }    
    /**
     * Capture the activation token from $uri
     * @param type $uri
     */
    protected function findActivationToken($uri)
    {
        if (preg_match('/\b\/activate\b/', $uri)){
            $parts = parse_url($uri);
            parse_str($parts['query'], $query);
            if (isset($query['token'])){
                $this->activateToken = $query['token'];
                logTrace(__METHOD__.' Activation request! token',$this->activateToken);
            }
        }
        return $this->activateToken;
    }    
}
