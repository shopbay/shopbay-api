<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

use app\components\ApiUserCredentials;
/**
 * Description of CustomerController
 *
 * @author kwlok
 */
class CustomerController extends LoginController 
{
    protected $identityClass = '\IdentityCustomer';
    protected $shopScope;//Not null user is on shop scope, and the value is the shop value
    /**
     * Authenticate user
     * @param type $username
     * @param type $password
     * @return type
     */
    public function authUser($username,$password)
    {
        $this->shopScope = $this->findShop($_SERVER['REQUEST_URI']);//return shop id
        if ($this->shopScope==null){
            $this->identityClass = '\IdentityUser';//non shop scoped user
            return parent::authUser ($username, $password);
        }
        else {
            try {
                $this->findActivationToken($_SERVER['REQUEST_URI']);
                $this->uname = $username;
                $this->pwd = $password;
                $cid = \IdentityCustomer::createCid($this->shopScope, $this->uname);
                $user = \app\models\CustomerUser::findUser($cid,$this->pwd,$this->identityClass,$this->activateToken);  
                $this->uid = $user->id;
                return $user;

            } catch (\CException $ex) {
                logError(__METHOD__.' error',$ex->getTraceAsString(),false);
                $this->disableLoggingGlobalPHPVars();
                return null;
            }
        }
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
            'shop_id'=>$this->shopScope,
        ]);
    }    
    /**
     * Set user credentials to request for token
     */
    protected function setUserCredentials()
    {
        parent::setUserCredentials();
        if ($this->shopScope){
            //encode shop scope onto username
            $_POST['username'] = ApiUserCredentials::encodeUsername(\Account::TYPE_CUSTOMER,$this->shopScope,$this->uname);
        }
    }
    /**
     * Capture the shop id from $uri
     * @param type $uri
     */
    protected function findShop($uri)
    {
        $shop = null;
        $parts = parse_url($uri);
        if (isset($parts['query'])){
            parse_str($parts['query'], $query);
            if (isset($query['shop'])){
                $shop = $query['shop'];
                logTrace(__METHOD__.' shop',$shop);
            }
        }
        return $shop;
    }       
}
