<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use Sii;
use Yii;
use app\models\ApiModelTrait;
Yii::import('common.modules.plans.models.PlanItem');
/**
 * Description of PlanItem
 *
 * @author kwlok
 */
class PlanItem extends \PlanItem 
{
    use ApiModelTrait;
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
        //Api output fields
        $this->fields = ['id','name'];
        //Insertable fields
        $this->insertables = [
            'plan_id','name',
        ];        
        //Updatables fields
        $this->updatables = [
            'id','name',
        ];        
    }  
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('id', 'required','on'=>\app\models\ApiModel::SCENARIO_UPDATE),
            array('id', 'ruleChildId','on'=>\app\models\ApiModel::SCENARIO_UPDATE),
        ));
    } 

    public function ruleChildId($attribute,$params)
    {
        $condition = 'plan_id='.$this->plan_id.' AND id='.$this->$attribute;
        logTrace(__METHOD__.' condition >> '.$condition,$this->attributes);
        if (!\PlanItem::model()->exists($condition))
            $this->addError($attribute,Sii::t('sii','ID not found'));
    }
}
