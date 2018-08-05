<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use Yii;
/**
 * Description of RbacManager
 * 
 * @author kwlok
 */
class RbacManager extends yii\base\BaseObject
{
    /**
     * Yii authManager
     * @var type 
     */
    protected $auth;
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        $this->auth = Yii::$app->authManager;
    }    
    /**
     * Create role
     * @param type $name
     */
    public function createRole($name)
    {
        $role = $this->auth->getRole($name);
        if ($role==null){
            $role = $this->auth->createRole($name);
            $this->auth->add($role);
            logInfo(__METHOD__." role $name created ok");
        }    
        return $role;
    }
    /**
     * Create permissions based on api models
     * @param array $permissionModels
     * @param type $rule if not null, permission created will be associated with this rule
     * @param type $role if not null, permission created will be added to role
     * @param type $permissionAttribute the attribute used to retrieve permission name
     */
    public function createPermissions($permissionModels,$rule,$role=null,$permissionAttribute='name') 
    {
        foreach ($permissionModels as $item) {
            $permission = $this->auth->getPermission($item->{$permissionAttribute});
            if ($permission==null){
                $permission = $this->auth->createPermission($item->{$permissionAttribute});
                $permission->ruleName = $rule->name;//associate rule with permission
                $this->auth->add($permission);
                logInfo(__METHOD__." permission $permission->name created ok");
            }
            if (isset($role)){
                if (!$this->auth->hasChild($role, $permission))
                    $this->auth->addChild($role, $permission);
            }
        }
    }
    /**
     * A handy method to combine createRole and createPermissions
     * @param type $roleName
     * @param type $permissionModels
     * @param type $permissionAttribute
     */
    public function createRoleAndPermissions($roleName,$permissionModels,$rule,$permissionAttribute='name') 
    {
        $this->createPermissions($permissionModels,$rule,$this->createRole($roleName),$permissionAttribute);
    }
    /**
     * Assign role to user
     * @param type $roleName
     * @param type $user
     */
    public function assignRole($roleName,$user)
    {
        $this->auth->assign($this->auth->getRole($roleName), $user);
        logInfo(__METHOD__." $roleName to user $user ok");
    }
    /**
     * Revoke role to user
     * @param type $roleName
     * @param type $user
     */
    public function revokeRole($roleName,$user)
    {
        $this->auth->revoke($this->auth->getRole($roleName), $user);
        logInfo(__METHOD__." $roleName from user $user ok");
    }    
}
