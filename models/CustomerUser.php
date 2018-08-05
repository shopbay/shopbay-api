<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

use Yii;
/**
 * Description of CustomerUser
 * Referenced as Yii::$app->user->identity
 * 
 * @author kwlok
 */
class CustomerUser extends User
{
    public $shop;
    /**
     * This is invoked when login form is presented, and by grant_type = password
     * @see modules\oauth2server\LoginController
     */
    public static function findUser($cid,$password,$identityClass='\IdentityCustomer',$token=null)
    {
        $customerData = \IdentityCustomer::parseCid($cid);
        //validate activation token if any
        $activate = static::_isActivationRequest($customerData['username'], $token,['shop_id'=>$customerData['shop']]);
        if ($activate!=true && $activate==-1){
            return null;//exit
        }
        
        //proceed to authentication
        $user = static::_invokeAuthService(static::createIdentity($cid, $password, $identityClass), false, true, true, $activate);//turn on audit for login
        if (is_array($user)) {
            logInfo(__METHOD__.' login as '.$identityClass.' user='.$user['id'].' shop='.$customerData['shop'],$user);
            return new static($user);
        }
        return null;
    }    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $account = \CustomerAccount::model()->find('id='.\Account::decodeId($id));
        if ($account!=null){
            $user = [
                'id' => $id,//need to use back encoded id
                'username' => $account->email,
                'identityType' => self::_parseIdentityType($account),
                'roles' => $account->getRoles(),
                'activate' => $account->pendingActivation(),
                'shop' => $account->shop_id,
            ];
            $identityClass = '\\'.$user['identityType'];
            $cid = \IdentityCustomer::createCid($account->shop_id, $account->email);
            if (static::_invokeAuthService(static::createIdentity($cid, $account->password, $identityClass), true)){
                logInfo(__METHOD__.' Login as '.$user['identityType'].' user='.$account->uid);
                return new static($user);
            }
        }
        return null;
    }    
    /**
     * Finds user by username
     * @see ApiUserCredentials::checkUserCredentials() 
     * @param  string $cid
     * @return static|null
     */
    public static function findByUsername($cid)
    {        
        $customerData = \IdentityCustomer::parseCid($cid);
        $account = \CustomerAccount::model()->findByPk([
            'shop_id'=>$customerData['shop'],
            'email'=>$customerData['username'],
        ]);
        if ($account!=null){
            $user = [
                'id' => \Account::encodeId(\Account::TYPE_CUSTOMER,$account->id),
                'username' => $account->email,
                'identityType' => self::_parseIdentityType($account),
                'roles' => $account->getRoles(),
                'activate' => $account->pendingActivation(),
                'shop' => $customerData['shop'],
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
     * @param  string  $shop 
     * @param  string  $password password to validate
     * @param  boolean $activate if for account activation
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password,$activate=false)
    {
        $cid = \IdentityCustomer::createCid($this->shop, $this->username);
        if (static::_invokeAuthService(static::createIdentity($cid, $password, $this->identityType), false, false , false, $activate)){
            logInfo(__METHOD__.' passed! for '.$this->identityType.' user='.$this->id);
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * Create identity instance based on identity type
     * @param type $shop
     * @param type $username
     * @param type $password
     * @param type $identityClass
     * @return \app\models\identityClass
     */
    public static function createIdentity($cid,$password,$identityClass='\IdentityCustomer')
    {
        return new $identityClass($cid,$password);        
    }
    /**
     * Determine user identity type
     * @param type $account
     * @return string 
     */
    protected static function _parseIdentityType($account)
    {
        return 'IdentityCustomer';
    }
    /**
     * Internal activation token validation using service manager
     * @see CustomerManager::validateActivationToken()
     * @return boolean
     */
    protected static function _validateActivationToken($token,$extraCriteria=[])
    {
        return Yii::app()->getModule('customers')->serviceManager->validateActivationToken($token,$extraCriteria);
    }
    
    protected static function _isActivationRequest($username,$token,$params=[])
    {
        //validate activation token if any
        if (isset($token)){
            $account = static::_validateActivationToken($token, $params);
            if ($account!=null && $account->email==$username){
                logInfo(__METHOD__.' Activation token and account email matched!');
                return true;//valid token and account name
            }
            else {
                logError(__METHOD__.' Activation account email mismatched!');
                return -1;//exit! unlikely to happen unless got system bug?
            }
        }
        else
            return false;
    }
    
}
