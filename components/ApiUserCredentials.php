<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\components;

use app\models\User;
use app\models\CustomerUser;
/**
 * Description of ApiUserCredentials
 *
 * @author kwlok
 */
class ApiUserCredentials implements \OAuth2\Storage\UserCredentialsInterface
{
    public static $separator = ':';
    /**
     * This validates the correct username and password 
     * Implemented for Oauth2 Interface
     * @see OAuth2\GrantType\UserCredentials::validateRequest()
     */
    public function checkUserCredentials($username, $password) 
    {
        $user = $this->findUser($username);
        if (!isset($user)) {
            return false;
        }        
        return $user->validatePassword($password,$user->activate);
    }
    /**
     * Implemented for Oauth2 Interface
     * @see OAuth2\GrantType\UserCredentials::validateRequest()
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username) 
    {
        $user = $this->findUser($username);
        return ['user_id' => $user->getId()];        
    }
    /**
     * Find the correct user 
     * @param type $username
     * @return type
     */
    protected function findUser($username)
    {
        $user = static::decodeUsername($username);
        if ($user['class']=='CustomerUser'){
            $cid = \IdentityCustomer::createCid($user['shop'], $user['username']);
            $user = CustomerUser::findByUsername($cid);
        }
        else {
           $user = User::findByUsername($user['username']);
        }
        return $user;
    }
    
    public static function encodeUsername($type,$scope,$username)
    {
        return $type.ApiUserCredentials::$separator.$scope.ApiUserCredentials::$separator.$username;
    }
    
    public static function decodeUsername($encodedName)
    {
        $data = explode(static::$separator, $encodedName);
        if (isset($data[1]) && isset($data[2])){
            if ($data[0]==\Account::TYPE_CUSTOMER){
                return [
                    'class' => 'CustomerUser',
                    'shop' => $data[1],
                    'username' => $data[2],
                ];
            }
        }
        else
            return [
                'class' => 'User',
                'username' => $encodedName,
            ];
    }
    
}
