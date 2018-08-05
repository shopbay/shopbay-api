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
Yii::import('common.modules.shops.models.ShopAddress');
/**
 * Description of ShopAddress
 *
 * @author kwlok
 */
class ShopAddress extends \ShopAddress
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
        $this->fields = ['address1','address2','postcode','city','state','country'];
        //Insertable fields
        $this->insertables = ['address1','address2','postcode','city','state','country'];        
        //Updatables fields
        $this->updatables = ['address1','address2','postcode','city','state','country'];        
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('country', 'ruleLocales','method'=>'getCountries'),
            array('state', 'ruleLocales','method'=>'getStates'),
        ));
    }  
    /**
     * Perform shop locales related validation
     */
    public function ruleLocales($attribute,$params)
    {
        $addError = function($attribute) {
            $this->addError($attribute,Sii::t('sii','Invalid {attribute}',array('{attribute}'=>$attribute)));
        };
        if ($attribute=='state'){
            if (\SLocale::{$params['method']}($this->country,$this->$attribute)==Sii::t('sii','unset')){
                $addError($attribute);
            }
        }
        else {
            if (\SLocale::{$params['method']}($this->$attribute)==Sii::t('sii','unset'))
                $addError($attribute);
        }
    }      
}
