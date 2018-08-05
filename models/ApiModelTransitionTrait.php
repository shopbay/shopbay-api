<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

use app\modules\v1\models\Transition;
/**
 * Description of ApiModelTransitionTrait
 * A reference model on common\modules\tasks\models\Transition
 *
 * @author kwlok
 */
trait ApiModelTransitionTrait 
{
    public $transition;
    /**
     * Transition validation rule
     */
    public function ruleTransition($attribute, $params)
    {
        if (!$this->$attribute->validate())
           $this->addError($attribute,\Helper::implodeErrors($this->$attribute->errors));
    }    
    /**
     * A method to prepare data for workflow transition processing
     * Parse object fields and populate field with correct values
     */
    public function prepareTransition($action,$rawBody,$currentAttributes,$oldAttributes)
    {
        $this->inputData = isset($rawBody)?$rawBody:[];
        $this->setIsNewRecord(false);//update record so set to false
        $this->setScenario(ApiModel::SCENARIO_TRANSITION);
        \Yii::configure($this,$currentAttributes);
        $this->setUpdatableRules([]);
        $this->setOldAttributes($oldAttributes);
        logTrace(__METHOD__.' '.get_class($this).' old attributes ',$this->oldAttributes);
        logTrace(__METHOD__.' '.get_class($this).' current attributes',$this->attributes); 
        if (!empty($this->inputData)){//attach transition object to model
            $transition = new Transition();
            $transition->obj_type = $this->tableName();
            $transition->obj_id = $this->id;
            $transition->action = ucfirst($action);
            //swap condition key if any
            $internalInputFields = $this->parseInputFields($this->inputData);
            $transition->setInsertableRules(array_keys($internalInputFields));
            foreach ($internalInputFields as $attribute => $value) {
                if ($transition->hasAttribute($attribute))
                    $transition->$attribute = $value;
            }
            $this->transition = $transition;
            logTrace(__METHOD__.' '.get_class($this).' transition attributes',$this->transition->attributes); 
            //Add transition validation rule
            $this->extraRules = array_merge($this->extraRules,[
                ['transition', 'ruleTransition','on'=> ApiModel::SCENARIO_TRANSITION],
            ]);
        }
    }
    /**
     * Swap condition key if any, based on transitionConditionsMap
     * @see self::transitionConditionsMap()
     * @param type $inputFields
     * @return type
     */
    public function parseInputFields($inputFields)
    {
        $swap = [];
        foreach ($inputFields as $field => $value) {
            if (isset($this->transitionConditionsMap()[$field]))
                $swap[$this->transitionConditionsMap()[$field]] = $value;
            else
                 $swap[$field] = $value;
        }
        logTrace(__METHOD__.' swapped fields',$swap);
        return $swap;
    }
    /**
     * Transition conditions map. Default follow schema field name
     * Format: ['<input>'=>'<actual field>']
     * @return type
     */
    public function transitionConditionsMap()
    {
        return [
            'condition1'=>'condition1',
            'condition2'=>'condition2',
        ];
    }
}
