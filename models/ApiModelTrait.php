<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

use Sii;
/**
 * Description of ApiModelTrait
 *
 * @author kwlok
 */
trait ApiModelTrait 
{
    use ApiModelCreateTrait, ApiModelViewTrait, ApiModelUpdateTrait, ApiModelTransitionTrait;  
    /*
     * Api model extra rules
     * A convienent placeholder to keep rules that can be used by other traits, e.g. ApiModelTransitionTrait
     */
    public $extraRules = [];    
    /*
     * Api output fields
     */
    public $fields = [];    
    /*
     * Api input data derived from HTTP rawBody
     */
    public $inputData;
    /*
     * Insertable fields
     */
    public $insertables = [];        
    /*
     * Updatables fields
     */
    public $updatables = []; 
    /*
     * A callback to get language keys
     */
    public $languageKeysCallback = 'getLanguageKeys';    
    /*
     * A callback to get language attribute
     */
    public $languageAttributeCallback = 'language';       
    /**
     * Set insertable rules
     * @param type $inputFields this should be client input fields
     */
    public function setInsertableRules($inputFields)
    {
        $this->setInsertables([
            'allow'=>$this->insertables,
            'input'=>$inputFields,
        ]);
    } 
    /**
     * Set updatable rules
     * @param type $inputFields this should be client input fields
     */
    public function setUpdatableRules($inputFields)
    {
        $this->setUpdatables([
            'allow'=>$this->updatables,
            'input'=>$inputFields,
        ]);
    } 
    /**
     * A method to prepare data for service processing
     * Parse object fields and populate field with correct values
     */
    public function prepareService()
    {
        logTrace(__METHOD__.' '. get_class($this).' old attributes ',$this->oldAttributes);
        foreach ($this->localeFields as $field) {
            $this->parseLocaleFields($field);
        }
        logTrace(__METHOD__.' '. get_class($this).' current attributes',$this->attributes);  
        foreach ($this->childFields as $field => $config) {
            $this->parseChildFields($field,$config);
        }
    }    
    /**
     * Serialize output for Api use
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->fields as $field => $value) {
            if (is_array($value)){
                switch ($value['type']) {
                    case 'process':
                        $result[$field] = \Process::getText($this->$field);
                        break;
                    case 'time':
                        $result[$field] = date('Y-m-d\TH:i:s', $this->$field);
                        break;
                    case 'locale':
                        $result[$field] = $this->$field==null?"":json_decode($this->$field, true);
                        break;
                    case 'parent':
                        if (isset($value['referenceAttribute']))
                            $result[$field] = $this->$field->{$value['referenceAttribute']};
                        else {
                            $attributes = $this->$field->attributes;
                            //remove fields that not intent to return
                            unset($attributes['account_id']);
                            unset($attributes['status']);
                            unset($attributes['create_time']);
                            unset($attributes['update_time']);
                            $result[$field] = $attributes;           
                        }
                       break;
                    case 'child':
                        if (is_array($this->$field)){
                            foreach ($this->$field as $key => $value) {
                                $result[$field][] = $value->toArray();
                            }
                        }
                        else {
                            if (isset($this->$field))
                                $result[$field] = $this->$field->toArray();
                            else
                                $result[$field] = "";
                        }
                        break;
                    case 'image':
                        $result[$field] = $this->getImageOriginalUrl();
                        break;
                    case 'json':
                        $result[$field] = json_decode($this->$field);
                        break;
                    default://for string type as well
                        $result[$field] = $this->$field==null?"":$this->$field;
                        break;
                }
            }
            else {
                $result[$value] = $this->_parseValue($this->$value);//$value is also field (index array)   
            }
        }
        //logTrace(__METHOD__,$result);
        return $result;
    }  
    /**
     * Auto select locale attributes
     * @return type
     */
    protected function getLocaleFields()
    {
        $locales = [];
        foreach ($this->fields as $field => $value) {
            if (is_array($value) && $value['type']=='locale')
                $locales[] = $field;
        }
        return $locales;        
    }
    /**
     * Auto select child attributes
     * @return type
     */
    protected function getChildFields()
    {
        $fields = [];
        foreach ($this->fields as $attribute => $value) {
            if (is_array($value) && $value['type']=='child')
                $fields[$attribute] = $this->fields[$attribute];
        }
        return $fields;        
    } 
    /**
     * This method check locale attributes to have correct value populated to be stored in db
     * @param type $attribute locale attribute
     * @throws \CException
     */
    protected function parseLocaleFields($attribute)
    {
        $localeValue = [];
        if (is_string($this->$attribute)){//either user submit as string or in json_encode
            //populate missing locales based on language keys if any
            $populateMissingLang = function ($defaultValue,$locales=[]) {
                foreach ($this->{$this->languageKeysCallback}() as $lang) {
                    if ($lang==$this->{$this->languageAttributeCallback})
                        $locales[$lang] = $defaultValue;
                    else
                        $locales[$lang] = "";
                }
                return $locales;
            };

            if ($this->getScenario() == ApiModel::SCENARIO_CREATE){
                $localeValue = $populateMissingLang($this->$attribute);
            }
            elseif ($this->getScenario() == ApiModel::SCENARIO_UPDATE && $this->hasOldAttributes){
                if (strcasecmp($this->oldAttributes[$attribute],$this->$attribute)!=0){
                   //e.g. when "name" is specified in raw body
                    $localeValue = $populateMissingLang($this->$attribute);
                }
                else {
                   //e.g. when "name" is not specified in raw body, use back old attributes
                    $localeValue = json_decode($this->oldAttributes[$attribute],true);
                }
            }            
        }
        elseif (is_array($this->$attribute)) {//array
            //check valid locales
            foreach (array_keys($this->$attribute) as $locale) {
                if (!in_array($locale, $this->{$this->languageKeysCallback}()))
                    throw new \CException(Sii::t('sii','Unknown locale {locale}',['{locale}'=>$locale]));
            }
            //populate locale names
            foreach ($this->{$this->languageKeysCallback}() as $lang) {
                if (in_array($lang, array_keys($this->$attribute)))
                    $localeValue[$lang] = $this->$attribute[$lang];
                else {
                    if ($this->hasOldAttributes){
                        $oldLocales = json_decode($this->oldAttributes[$attribute],true);
                        $localeValue[$lang] = $oldLocales[$lang];//if not found, repsect old value
                    }
                    else
                        $localeValue[$lang] = '';//if not found, set to empty string
                }
            }
        }
        
        if (!empty($localeValue))
            $this->$attribute = json_encode($localeValue);        
    }
    /**
     * This method check child attributes to have correct value populated to be stored in db
     * @param type $attribute child attribute
     * @throws \CException
     */
    protected function parseChildFields($attribute,$config)
    {
        $childModelClass = $config['class'];    
        $inputData = isset($this->inputData[$attribute])?$this->inputData[$attribute]:[];
        
        $createChild = function($inputData) use ($childModelClass) {
            $model = new $childModelClass();
            $model->prepareCreate($inputData);
            return $model;
        };
        $updateChild = function($inputData,$childAttributes) use ($attribute, $childModelClass) {
            $model = new $childModelClass();
            $model->prepareUpdate($inputData,$childAttributes,$this->oldAttributes[$attribute]);
            return $model;
        };
        $findChild = function($id) use($attribute,$childModelClass,$updateChild) {
            foreach ($this->$attribute as $key => $child) {
                if ($id==$child->id){
                    return $updateChild([],$child->attributes);
                }
            }
            return null;
        };
        
        if (is_array($this->$attribute) && isset($config['hasMany']) && $config['hasMany']){
            $childs = [];
            //logTrace(__METHOD__.' child attributes',$this->$attribute);
            if (\yii\helpers\ArrayHelper::isIndexed($this->$attribute)){//meaning array of childs
                if ($this->getScenario() == ApiModel::SCENARIO_CREATE){
                    foreach ($this->$attribute as $key => $childAttributes) {
                        if (!empty($inputData) && isset($inputData[$key]))
                            $childs[] = $createChild($inputData[$key],$childAttributes);
                    }
                }
                if ($this->getScenario() == ApiModel::SCENARIO_UPDATE){
                    if (!empty($inputData)){
                        foreach ($inputData as $key => $inputValue) {
                            $id = isset($inputValue['id'])?$inputValue['id']:'';
                            unset($inputValue['id']);//remove id key as not needed in processing: create will have new id, update will use existing id
                            $child = $findChild($id);
                            if ($child!=null){
                                logTrace(__METHOD__.' '.get_class($child).' updating existing child',$child->attributes);
                                $childs[] = \Yii::configure($child, $inputValue);
                            }
                            else {
                                $inputValue[$config['foreignKey']] = $this->id;
                                logTrace(__METHOD__.' creating new child...',$inputValue);
                                $newChild = $createChild($inputValue);
                                //search newly assigned id
                                $criteria = new \CDbCriteria();
                                $criteria->addColumnCondition([
                                    $config['foreignKey']=>$this->id,
                                    $config['keyAttribute']=>$inputValue[$config['keyAttribute']],
                                ]);
                                logTrace(__METHOD__.' new child search id criteria',$criteria);
                                $baseModelClass = $config['baseModel'];
                                $baseModel = $baseModelClass::model()->find($criteria);
                                if ($baseModel!=null){
                                    $newChild->id = $baseModel->id;
                                }
                                logTrace(__METHOD__.' new child created',$newChild->attributes);
                                $childs[] = $newChild;
                            }
                        }
                    }
                }
            } 
            if (\yii\helpers\ArrayHelper::isAssociative($this->$attribute)){//meaning single child object
                if ($this->getScenario() == ApiModel::SCENARIO_CREATE)
                    $childs[] = $createChild($inputData);
                if ($this->getScenario() == ApiModel::SCENARIO_UPDATE)
                    $childs[] = $updateChild($inputData,$this->$attribute->attributes);
            }
            $this->$attribute = $childs;
        }
        else {
            if ($this->getScenario() == ApiModel::SCENARIO_CREATE && !empty($inputData)){
                $this->$attribute = $createChild($inputData);
                logTrace(__METHOD__.' '.get_class($this).'->'.$attribute.' '.$this->getScenario(),$this->$attribute->attributes);
            }
            if ($this->getScenario() == ApiModel::SCENARIO_UPDATE){
                $this->$attribute = $updateChild($inputData,$this->$attribute->attributes);
                logTrace(__METHOD__.' '.get_class($this).'->'.$attribute.' '.$this->getScenario(),$this->$attribute->attributes);
            }
        }
    }
    /**
     * Perform childs validation
     * params: 'foreignKey', 'hasMany' <- true if has many childs (in array form)
     */
    public function ruleChildField($attribute,$params)
    {
        if ($this->getScenario() == ApiModel::SCENARIO_CREATE){
            if ($this->$attribute==null && isset($params['mandatory']) && $params['mandatory']){
                $this->addError($attribute,Sii::t('sii','Field {attribute} must be set.',array('{attribute}'=>$attribute)));
                return;
            }
        }
            
        if ($this->$attribute!=null){
            //if has many childs
            if (isset($params['hasMany']) && $params['hasMany']){
                foreach ($this->$attribute as $key => $child) {
                    if ($this->getScenario() == ApiModel::SCENARIO_CREATE)
                        $child->{$params['foreignKey']} = 0;//assign dummy value for validation
                    if (!$child->validate()){
                        $this->addError($attribute,Sii::t('sii','{class}#{n} {errors}',array($key+1,'{class}'=>$child->displayName(),'{errors}'=>\Helper::implodeErrors($child->errors))));
                    }
                    else
                        $child->{$params['foreignKey']} = $this->id;//restore null value
                }
            }
            else { //if single child
                if ($this->getScenario() == ApiModel::SCENARIO_CREATE)
                    $this->$attribute->{$params['foreignKey']} = 0;//assign dummy value for validation
                if (!$this->$attribute->validate()){
                    $this->addError($attribute,\Helper::implodeErrors($this->$attribute->errors));
                }
                else
                    $this->$attribute->{$params['foreignKey']} = $this->id;//restore null value
            }
        }
    }      
    /**
     * Perform locales related validation
     */
    public function ruleLocales($attribute,$params)
    {
        if (\SLocale::{$params['method']}($this->$attribute)==Sii::t('sii','unset'))
            $this->addError($attribute,Sii::t('sii','Invalid {attribute}',array('{attribute}'=>$attribute)));
    }     
    /**
     * Parse value for proper type casting
     */
    private function _parseValue($value)
    {
        if ($value==null) 
            $value = "";
        elseif (\Helper::isInteger($value)) 
            $value = (int)$value;
        elseif (\Helper::isFloat($value)) 
            $value = round((float)$value,2);       
        return $value;
    }
}
