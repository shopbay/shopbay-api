<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use app\models\ApiModelTrait;
/**
 * Description of Plan
 *
 * @author kwlok
 */
class Plan extends \Plan 
{
    use ApiModelTrait, SubscriptionTrait;  
        /**
     * @see SActiveRecord
     */
    public $enableInsertableCheck = true;   
    public $enableUpdatableCheck  = true;  
    /**
     * Init class
     */
    public function init()
    {        
        parent::init();
        //Api extra rules
        $this->extraRules = [
            ['currency', 'ruleLocales','method'=>'getCurrencies'],
            ['items', 'ruleChildField','foreignKey'=>'plan_id','hasMany'=>true,'mandatory'=>true],
        ];
        //Api output fields
        $this->fields = [
            'id','name','type','recurring','price','currency','duration',
            'items'=>['type'=>'child','baseModel'=>'\PlanItem','foreignKey'=>'plan_id','keyAttribute'=>'name','class'=>'app\modules\v1\models\PlanItem','hasMany'=>true],
            'status'=>['type'=>'process'],
            'create_time'=>['type'=>'time'],
            'update_time'=>['type'=>'time'],
        ];
        //Insertable fields
        $this->insertables = [
            'name','type','recurring','duration','price','currency','items'
        ];        
        //Updatables fields
        $this->updatables = [
            'name','type','recurring','duration','price','items'
        ];            
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),$this->extraRules);
    } 
    /**
     * Transition conditions map - no change, follow schema field name
     * @return type
     */
    public function transitionConditionsMap()
    {
        return [
            'reason'=>'condition1',
            'remarks'=>'condition2',
        ];
    }      
  
}
