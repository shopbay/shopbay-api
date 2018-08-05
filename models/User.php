<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

use Yii;
/**
 * Description of User
 * Referenced as Yii::$app->user->identity
 * 
 * @author kwlok
 */
class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface, 
                                               \yii\filters\RateLimitInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;
    public $identityType;//the identity type
    public $roles;//stores user roles; can be applied into oauth scopes
    public $activate = false;//indicate is user is pending activation; True means yes
    /**
     * This is invoked when login form is presented, and by grant_type = password
     * @see modules\oauth2server\LoginController
     */
    public static function findUser($username,$password,$identityClass='\IdentityUser',$token=null)
    {
        //validate activation token if any
        $activate = static::_isActivationRequest($username, $token);
        if ($activate!=true && $activate==-1){
            return null;//exit
        }
        
        //proceed to authentication
        $user = static::_invokeAuthService(static::createIdentity($username, $password, $identityClass), false, true, true, $activate);//turn on audit for login
        if (is_array($user)) {
            logInfo(__METHOD__.' login as '.$identityClass.' user='.$user['id']);
            return new static($user);
        }
        return null;
    }    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        logInfo(__METHOD__." Searching for identity id $id ...");
        
        switch (\Account::getAccountClass($id)) {
            case 'CustomerAccount':
                logInfo(__METHOD__.' Logins as Customer Account',$id);
                return CustomerUser::findIdentity($id);
            case 'MerchantAccount':
                logInfo(__METHOD__.' Logins as Merchant Account',$id);
                //@todo
                return null;
                break;
            default://for 'Account'
                $account = \Account::model()->findByPk($id);
                if ($account!=null){
                    $user = [
                        'id' => $account->id,
                        'username' => $account->name,
                    ];
                    if ($account->isSystem){
                        logWarning(__METHOD__.' Logins as SYSTEM user',$id);
                        return new static($user);
                        //return null;//disallow SYSTEM user to login
                    }
                    else {
                        $user['identityType'] = self::_parseIdentityType($account);
                        $user['roles'] = $account->getRoles();//todo can be use as oauth scopes
                        $identityClass = '\\'.$user['identityType'];
                        if (static::_invokeAuthService(static::createIdentity($account->name, $account->password, $identityClass), true, false, false, false, true)){
                            logInfo(__METHOD__.' Login as '.$user['identityType'].' user='.$account->id);
                            return new static($user);
                        }
                    }
                }
                //when no conditions met, return null
                return null;
        }//end switch
    }
    /**
     * Finds user by username
     * @see ApiUserCredentials::checkUserCredentials() 
     * @param  string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {        
        $account = \Account::model()->find('name=:username or email=:username',[':username'=>$username]);
        if ($account!=null){
            $user = [
                'id' => $account->id,
                'username' => $account->name,
                'identityType' => self::_parseIdentityType($account),
                'roles' => $account->getRoles(),
                'activate' => $account->pendingActivation(),
            ];
            logTrace(__METHOD__.' found!',$user);
            return new static($user);
        }    
        return null;
    }    
    /**
     * Validates password
     * @see ApiUserCredentials::checkUserCredentials() 
     *
     * @param  string  $password password to validate
     * @param  boolean $activate if for account activation
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password,$activate=false)
    {
        if (static::_invokeAuthService(static::createIdentity($this->username, $password, $this->identityType), false, false , false, $activate)){
            logInfo(__METHOD__.' passed! for '.$this->identityType.' user='.$this->id);
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        //Not required for stateless RESTful application
    }
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        //Not required for stateless RESTful application
    }
    /**
     * Returns the maximum number of allowed requests and the time period (e.g., [100, 600] means there can be at most 100 API calls within 600 seconds).
     * @param type $request
     * @param type $action
     */
    public function getRateLimit($request, $action) 
    {
        return [1000, 86400]; // 1000 requests per day
    }
    /**
     * Returns the number of remaining requests allowed and the corresponding UNIX timestamp when the rate limit was last checked.
     * @param type $request
     * @param type $action
     */
    public function loadAllowance($request, $action) 
    {
        //Todo
    }
    /**
     * Saves both the number of remaining requests allowed and the current UNIX timestamp.
     * @param type $request
     * @param type $action
     * @param type $allowance
     * @param type $timestamp
     */
    public function saveAllowance($request, $action, $allowance, $timestamp) 
    {
        //Todo
    }
    /**
     * Implemented for Oauth2 Interface:
     * [1] Token is passed in via the api end point.
     * [2] If token is valid, use the user_id in $storageToken to retrieve user account @see static::findIdentity()
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $module = Yii::$app->getModule('oauth2');
        /**  
         * @return Array $storageToken
         * (
         *    [access_token] => ACCESS_TOKEN
         *    [client_id] => CLIENT_ID
         *    [user_id] => USER_ID
         *    [expires] => 1453006502
         *    [scope] => 
         * )
         */
        $storageToken = $module->server->getResourceController()->getToken();
        if (strcasecmp($token, $storageToken['access_token'])!=0){
            logError(__METHOD__.' token not matched! '.$token.' vs '.$storageToken['access_token']);//this should not happen else could be system or weird situation
            return null;
        }
        elseif ($storageToken['user_id']==null){
            logError(__METHOD__.' user_id not found! ',$storageToken);
            return null;
        }
        else {
            logInfo(__METHOD__.' token passed! '.$storageToken['access_token']);
            return static::findIdentity($storageToken['user_id']);
        }    
    }
    /**
     * Create identity instance based on identity type
     * @param type $username
     * @param type $password
     * @param type $identityClass
     * @return \app\models\identityClass
     */
    public static function createIdentity($username,$password,$identityClass='\IdentityUser')
    {
        return new $identityClass($username,$password);        
    }
    /**
     * Internal authentication using service manager
     * Perform auto cookie-less login, $duration=0, $recordActivity=false
     * @return boolean
     */
    protected static function _invokeAuthService($identity,$hash=false,$returnInfo=false,$audit=false,$activate=false,$oauthByToken=false)
    {
        return Yii::app()->getModule('accounts')->serviceManager->authenticate($identity,0,$audit,$hash,$returnInfo,$activate,$oauthByToken);
    }
    /**
     * Internal activation token validation using service manager
     * @see AccountManager::validateActivationToken()
     * @return boolean
     */
    protected static function _validateActivationToken($token,$extraCriteria=[])
    {
        return Yii::app()->getModule('accounts')->serviceManager->validateActivationToken($token,$extraCriteria);
    }
    /**
     * Determine user identity type
     * @param type $account
     * @return string 
     */
    protected static function _parseIdentityType($account)
    {
        if ($account->isAdmin)
            return 'IdentityAdmin';
        elseif ($account->isMerchant)
            return 'IdentityMerchant';
        else
            return 'IdentityUser';
    }
    
    protected static function _isActivationRequest($username,$token,$params=[])
    {
        //validate activation token if any
        if (isset($token)){
            $account = static::_validateActivationToken($token, $params);
            if ($account!=null && $account->name==$username){
                logInfo(__METHOD__.' Activation token and account username matched!');
                return true;//valid token and account name
            }
            else {
                logError(__METHOD__.' Activation account username mismatched!');
                return -1;//exit! unlikely to happen unless got system bug?
            }
        }
        else
            return false;
    }
}
