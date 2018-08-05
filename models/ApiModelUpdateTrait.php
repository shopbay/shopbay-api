<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

/**
 * Description of ApiModelUpdateTrait
 * 
 * Prequisite: app\models\ApiModelTrait
 * 
 * @author kwlok
 */
trait ApiModelUpdateTrait 
{
    /**
     * A method to prepare data for update service processing
     * Parse object fields and populate field with correct values
     */
    public function prepareUpdate($rawBody,$currentAttributes,$oldAttributes)
    {
        $this->setIsNewRecord(false);//update record so should be false
        $this->setScenario(ApiModel::SCENARIO_UPDATE);
        $this->inputData = isset($rawBody)?$rawBody:[];
        $this->setUpdatableRules(array_keys($this->inputData));
        $this->setOldAttributes($oldAttributes);
        //set $rawBody data as current attributes
        foreach ($currentAttributes as $attribute => $value) {
            if (isset($rawBody) && in_array($attribute,array_keys($rawBody)))
                $this->$attribute = $rawBody[$attribute];
            else
                $this->$attribute = $value;//when not found in rawBody, keep original value
        }
        foreach ($this->childFields as $field => $config) {
            if (isset($rawBody[$field]) && $this->hasAttribute($field))
                $this->$field = $rawBody[$field];
        }    
        $this->prepareService();
    }
    /**
     * Load old attributes from source model, including childs
     * @param type $sourceModel
     * @return type
     */
    public function prepareOldAttributes($sourceModel)
    {
        $oldAttributes = $sourceModel->oldAttributes;
        //include child attributes into old attributes as well
        foreach ($this->childFields as $field => $config) {
            if (is_array($sourceModel->$field)){
                foreach ($sourceModel->$field as $child) {
                    $oldAttributes[$field][] = $child->oldAttributes;
                }
            }
            else
                $oldAttributes[$field] = $sourceModel->$field->oldAttributes;
        } 
        return $oldAttributes;
    }
}
