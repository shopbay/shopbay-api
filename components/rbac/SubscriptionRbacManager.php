<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

/**
 * Description of SubscriptionRbacManager
 * 
 * To test if user have permission, use following:
 * 
 * ```php
 * if (\Yii::$app->user->can('plan_item_name')) {
 *   //have access
 * }       
 * ```
 * 
 * @author kwlok
 */
class SubscriptionRbacManager extends RbacManager
{
    /**
     * A handy method to combine createRole and createPermissions
     * @param type $roleName
     * @param type $permissionModels
     */
    public function createRoleAndPermissions($roleName,$permissionModels,$rule=null,$permissionAttribute='name') 
    {
        //first create permission level rule
        $rule = new SubscriptionRule;
        if ($this->auth->getRule($rule->name)==null)
            $this->auth->add($rule);    
        parent::createRoleAndPermissions($roleName,$permissionModels,$rule,$permissionAttribute);
    }    
}
