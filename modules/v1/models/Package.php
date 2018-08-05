<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use app\models\ApiModelTrait;
/**
 * Description of Package
 *
 * @author kwlok
 */
class Package extends \Package 
{
    use ApiModelTrait {
        toArray as traitToArray;
    }      
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
        //Api extra rules
        $this->extraRules = [];
        //Api output fields
        $this->fields = [
            'id','name','type','plans',
            'params'=>['type'=>'json'],
            'status'=>['type'=>'process'],
            'create_time'=>['type'=>'time'],
            'update_time'=>['type'=>'time'],
        ];
        //Insertable fields
        $this->insertables = [
            'name','type','plans','params',
        ];        
        //Updatables fields
        $this->updatables = [
            'name','type','plans','params',
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
    /**
     * Serialize output for Api use
     * Support array of plans output
     */
    public function toArray()
    {
        $result = $this->traitToArray();
        $plans = [];
        foreach (explode(',',$this->plans) as $plan) {
            $model = \Plan::model()->findByPk($plan);
            if ($model!=null && $model->IsApproved)
                $plans[] = $this->promotePlanModel($model);
        }
        $result['plans'] = $plans;
        return $result;
    }    
    /**
     * Convert plan model into api model
     * @param type $planModel
     * @return type
     */
    protected function promotePlanModel($planModel) 
    {
        $apiModel = \Yii::configure(new Plan(), $planModel->attributes);
        return $apiModel->prepareChilds($planModel);
    }
    
}
