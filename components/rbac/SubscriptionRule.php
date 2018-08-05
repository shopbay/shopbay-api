<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use yii\rbac\Rule;
use Feature;
use SubscriptionPlan;
/**
 * Checks if subscription matches user passed via params, and is not expired or suspended or cancelled
 *
 * @author kwlok
 */
class SubscriptionRule extends Rule
{
    public $name = 'SubscriptionRule';
    public $subscription;
    public $planItem;
    /**
     * @param string|integer $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params) 
    {
        logTrace(__METHOD__.' Checking permission '.$item->name.' ...',$params);
        
        $subscriptions = $params['subscriptions'];
            
        if ($subscriptions==null || empty($subscriptions)){
            logError(__METHOD__.' User has no subscription. Reject access!');
            return false;
        }

        $access = false;
        foreach ($subscriptions as $sub) {
            $this->subscription = $sub;
            logTrace(__METHOD__.' Subscription attributes',$this->subscription->attributes);
            
            if (isset($params['shop'])){//When shop filter (shop id) is present
                if ($this->subscription->shop_id==$params['shop']){//only check the target shop 
                    logTrace(__METHOD__.' checking target plan for shop',$this->subscription->shop_id);
                    $access = $this->checkPlanItem($this->subscription, $item->name);
                    break;
                }
            }
            else {
                //Else as long as any subscription contains the service item will return true
                $access = $this->checkPlanItem($this->subscription, $item->name);
                if ($access)//true
                    break;
            }
        }

        if (!$access){
            logInfo(__METHOD__.' permission not found, Reject access!');
            //return false;
            throw new SubscriptionException(Feature::getNameDesc(Feature::parseKey($item->name, 'name')));        
        }
        else
            return true;
    }
    /**
     * Check if subscription plan item (subscription service) exists
     * It loads plan item object as well
     * @param Subscription $subscription
     * @param string $itemName
     * @return boolean
     * @throws SubscriptionException
     */
    protected function checkPlanItem($subscription,$itemName) 
    {
        $this->planItem = SubscriptionPlan::findItem($subscription, $itemName);
        return $this->planItem==null ? false : $this->planItem->plan->isApproved;//the plan must be approved
    }
    /**
     * Find feature param
     * Default search into local plan first, if not found then fall back to master plan 
     * @param type $field
     * @return boolean
     */
    protected function findFeatureParam($field) 
    {
        return SubscriptionPlan::findItemParam($this->planItem, $field);
    }    
}
