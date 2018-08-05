<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use app\components\rbac\SubscriptionRule;
use Feature;
/**
 * Description of SubscriptionUpperLimitRule
 *
 * @author kwlok
 */
class SubscriptionUpperLimitRule extends SubscriptionRule 
{
    public $name = 'SubscriptionUpperLimitRule';
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
            
            $limit = $this->findUpperLimit();
            if ($limit == Feature::$unlimited){
                logInfo(__METHOD__.' Unlimited. Grant access!');
                return true;
            }
            
            $modelClass = $this->findFeatureParam(Feature::$modelClass);
            $mineFilter = $this->findFeatureParam(Feature::$mineFilter);
            $modelFilter = $this->findFeatureParam(Feature::$modelFilter);
            
            //Check if to select which finder base
            if (isset($mineFilter)&&!$mineFilter)//false
                $finder = $modelClass::model();//not calling mine() filter
            else
                $finder = $modelClass::model()->mine($user);
            
            //Check if to apply model filtering   
            $enableModelFilter = false;
            if (isset($modelFilter)){
                $filterType = strtolower(substr($modelFilter, strlen('locate')));//$modelFilter method name must be start with 'locateXXX', e.g. locateShop
                logInfo(__METHOD__." Model $modelClass with filter type $filterType");
                if (isset($params[$filterType]) && $params[$filterType]>0){//model filter value must be greater than zero to exist
                    $finder = $finder->{$modelFilter}($params[$filterType]);
                    $enableModelFilter = true;
                }
            }
            
            //Check if to apply period filtering
            $period = $this->findFeatureParam(Feature::$period);
            if (isset($period)){
                //limit is count per period
                $timestamp = $this->parsePeriod($period);
                logInfo(__METHOD__." Getting record created within $period, timestamp=",$timestamp);
                $finder = $finder->createdAfter($this->parsePeriod($period));
            }
            
            //Check if to apply counter filtering
            $counterFilter = $this->findFeatureParam(Feature::$counterFilter);
            if (isset($counterFilter)){
                $finder = $finder->{$counterFilter}();
                logInfo(__METHOD__." Apply counter filter",$counterFilter);
            }
            
            //Compute current total count
            $totalItemCount = $finder->all()->count();
            
            if (!$enableModelFilter){//model filter is not invoked, this is new object, so the existing total count is set to zero
                $totalItemCount = 0;
                logInfo(__METHOD__.' Set current item count to zero for new object',$totalItemCount);
            }
            
            //Perform uppper limit check
            logTrace(__METHOD__." Checking $modelClass upper limit ($limit) vs current total count ($totalItemCount)");
            if ($totalItemCount < $limit){
                logInfo(__METHOD__." $modelClass is within limit ($limit). Grant access!");
                return true;
            }
            
            logInfo(__METHOD__." $modelClass exceeds limit ($limit). Reject access!");

            throw new SubscriptionUpperLimitException($limit);
        }
    }
    /**
     * @param type relative format
     * @return timestamp
     */
    protected function parsePeriod($time)
    {
        if ($time=='1 month')
            return strtotime(date('Y-m-01 0:00',strtotime('this month')));
        else {
            $month = explode(' ', 'months');
            $offset = $month - 1;
            return strtotime(date('Y-m-01 0:00',strtotime("$offset months ago")));
        }
    }
    /**
     * Find upper limit of plan item
     * @return type
     */
    protected function findUpperLimit()
    {
        $limit = $this->findFeatureParam(Feature::$upperLimit);
        return isset($limit)? $limit : -1;//-1 meaning no access (limit will never go negative)
    }

}
