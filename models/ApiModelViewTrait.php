<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

use Yii;
/**
 * Description of ApiModelViewTrait
 *
 * @author kwlok
 */
trait ApiModelViewTrait 
{
    /**
     * A method to prepare data for view/index service processing
     * Parse object fields and populate field with correct values
     */
    public function prepareChilds($sourceModel,$returnArray=true) 
    {
        foreach ($this->childFields as $field => $config) {
            if (is_array($sourceModel->$field) && isset($config['hasMany']) && $config['hasMany']){
                $childs  = [];
                foreach ($sourceModel->$field as $record) {
                    $child = new $config['class']();
                    $childs[] = Yii::configure($child, $record->attributes);
                }
                $this->$field = $childs;
            }
            else {
                $child = new $config['class']();
                if (isset( $sourceModel->$field))
                    $this->$field = Yii::configure($child, $sourceModel->$field->attributes);
            }
        }
        if ($returnArray)
            return $this->toArray();
        else
            return $this;
    }
}
