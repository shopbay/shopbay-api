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
//below import is required by child class
Yii::import('common.modules.shops.models.ShopAddress');

/**
 * Description of Shop
 * 
 * @author kwlok
 */
class Shop extends \Shop 
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
        $this->fields = [
            'id',
            'name'=>['type'=>'locale'],
            'tagline'=>['type'=>'locale'],
            'slug','contact_person','contact_no','email',
            'timezone','language','currency','weight_unit',
            'address'=>['type'=>'child','class'=>'app\modules\v1\models\ShopAddress','hasMany'=>false],
            'status'=>['type'=>'process'],
            'create_time'=>['type'=>'time'],
        ];
        //Insertable fields
        $this->insertables = [
            'name','tagline','slug','contact_person','contact_no','email',
            'timezone','language','currency','weight_unit',
            'address',
        ];        
        //Updatables fields
        $this->updatables = [
            'name','tagline','contact_person','contact_no','email',
            'timezone','language',
            'address',
        ];   
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['name', 'ruleLanguageField','max'=>50,'on'=> \app\models\ApiModel::SCENARIO_CREATE],
            ['address', 'ruleChildField','foreignKey'=>'shop_id'],
            ['currency', 'ruleLocales','method'=>'getCurrencies'],
            ['timezone', 'ruleLocales','method'=>'getTimeZones'],
            ['language', 'ruleLocales','method'=>'getLanguages'],
            ['weight_unit', 'ruleLocales','method'=>'getWeightUnits'],
        ]);
    }    
    /**
     * This validates the language field and make sure the default language field cannot be blank
     * and also check its maximum length allowed
     * @param type $attribute
     * @param type $params
     */
    public function ruleLanguageField($attribute,$params)
    {
        $field = json_decode($this->$attribute,true);
        foreach ($this->{$this->languageKeysCallback}() as $lang) {
            if ($lang==$this->language){
                if (empty($field[$lang]))
                    $this->addError($attribute,Sii::t('sii','{attribute} cannot be blank.',['{attribute}'=>$this->getAttributeLabel($attribute)]));
                elseif (!empty($field[$lang]) && strlen($field[$lang])> $params['max'])
                    $this->addError($attribute,Sii::t('sii','{attribute} exceeds the maximum length allowed: {max}',['{max}'=>$params['max'],'{attribute}'=>$this->getAttributeLabel($attribute)]));
                
            }
        }
    }     
    
//    /**
//     * Call back to delete childs for hard delete
//     * @var boolean 
//     */
//    protected $deleteChildsCallback = 'deleteChilds';    
//    /**
//     * Delete shop childs (such as setting, address, design etc)
//     */
//    public function deleteChilds()
//    {
//        if ($this->address!=null)
//            $this->address->delete();
//    }    
}
