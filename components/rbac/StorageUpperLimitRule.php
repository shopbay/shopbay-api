<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use app\components\rbac\SubscriptionRule;
use Sii;
use Feature;
/**
 * Description of StorageUpperLimitRule
 * Note: Storage upper limit is read from two places;
 * First (to be implemented unless got valid use case), load from account level limit cache (@see ConfigAccount), if not exist, set account limit to limit derived from Feature
 * Next, load from shop level plan limit 
 * Next is the master plan limit reading from Feature cache (if exists)
 * 
 * IMPORTANT:
 * To change plan storage limit, one must do TWO THINGS:
 * 1) Change the Feature limit (and clear cache) - Done at Admin app \ Feature
 * 2) Change again the shop level limit - Done at Admin app \ ConfigAccount
 * 3) (Not implemented - check code below) Change again the account level limit (and clear cache) - Done at Admin app \ ConfigAccount
 * @todo Make a helper method to clear all account limits affected by the storage limit at plan level change.
 * 
 * @author kwlok
 */
class StorageUpperLimitRule extends SubscriptionRule 
{
    public $name = 'StorageUpperLimitRule';
    /**
     * @param string|integer $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params) 
    {
        //must pass SubscriptionRule first
        if (parent::execute($user, $item, $params)){
            
            $subscriptions = $params['subscriptions'];
            
            $totalLimit = $this->findStorageLimit($subscriptions);
            if ($totalLimit == Feature::$unlimited){
                logInfo(__METHOD__.' Unlimited. Grant access!');
                return true;//break here
            }
            
            /**
             * @todo
             * Below code is used to have account level param on top of shop level subscription
             * Not needed now as we already have master and shop plan level, unless got valid use case
             * But the code below is overwriting account level limit with $totalLimit, so it could be redundant code 
             */
//            $myLimit = \ConfigAccount::getSetting($user,\ConfigAccount::MEDIA_STORAGE_LIMIT,$totalLimit);
//            if ($myLimit!=$totalLimit){//overwrite latest $totalLimit (user might have subscribe to new plans)
//                $myLimit = \ConfigAccount::setSetting($user,\ConfigAccount::BUSINESS,\ConfigAccount::MEDIA_STORAGE_LIMIT,$totalLimit);
//            }
            $myLimit = $totalLimit;//comment off this line and comment on code segment above to enable acocunt level
            
            $currentStorageSize = \Media::getTotalSize($user);
            logTrace(__METHOD__." Checking storage upper limit ($myLimit) vs current stoarage size ($currentStorageSize)");
            if ($currentStorageSize < $myLimit){
                logInfo(__METHOD__.' Storage is within limit ('.$myLimit.'). Grant access!');
                return true;
            }
            
            logInfo(__METHOD__.' Storage exceeds limit ('.$myLimit.'). Reject access!');

            throw new StorageUpperLimitException(Sii::t('sii','Storage exceeds {limit}',['{limit}'=>\Helper::formatBytes($myLimit)]));
        }
    }
    /**
     * Find storage TOTAL upper limit of plan item
     * @param array $subscriptions All subscriptons user is subscribing to
     * @return type
     */
    protected function findStorageLimit($subscriptions)
    {
        $totalLimit = 1;//starts with 1 byte to indicate almost zero storage; Cannot put zero as Feature::$unlimited uses zero as indicator
        //Combining all the storage limit of all subscribed plans
        foreach ($subscriptions as $sub) {
            $storageService = \SubscriptionPermission::model()->fuzzySearch($sub->plan->name,Feature::patternize(Feature::$hasStorageLimitTierN));
            $this->checkPlanItem($sub, $storageService);//to load plan item
            $limit = $this->findFeatureParam(Feature::$upperLimit);
            if ($limit == Feature::$unlimited){
                $totalLimit = Feature::$unlimited;
                logInfo(__METHOD__.' Unlimited found');
                break;
            }
            else {
                $totalLimit += $limit;
                logInfo(__METHOD__." Found plan $sub->plan_id has limit '$limit', add to total",$totalLimit);
            }
        }        
        return $totalLimit;
    }    
}
