<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

/**
 * Description of ApiModelCreateTrait
 * 
 * Prequisite: app\models\ApiModelTrait
 * 
 * @author kwlok
 */
trait ApiModelCreateTrait 
{
    /**
     * A method to prepare data for create service processing
     * Parse object fields and populate field with correct values
     */
    public function prepareCreate($rawBody)
    {
        $this->setScenario(ApiModel::SCENARIO_CREATE);
        $this->inputData = isset($rawBody)?$rawBody:[];
        $this->setInsertableRules(array_keys($this->inputData));
        \Yii::configure($this,$this->inputData);
        $this->prepareService();
    }
}
