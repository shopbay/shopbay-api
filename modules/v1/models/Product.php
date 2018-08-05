<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use Sii;
use app\models\ApiModelTrait;
/**
 * Description of Product
 *
 * @author kwlok
 */
class Product extends \Product 
{
    use ShopTrait;
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
        //Api output fields
        $this->fields = [
            'id','shop_id','brand_id',
            'code'=>['type'=>'string'],
            'name'=>['type'=>'locale'],
            'slug','unit_price','weight',
            'image'=>['type'=>'image'],
            'spec'=>['type'=>'locale'],
            'status'=>['type'=>'process'],
            'create_time'=>['type'=>'time'],
        ];
        //Insertable fields
        $this->insertables = [
            'shop_id','brand_id','code','name','slug','unit_price','weight','spec','image',
        ];        
        //Updatables fields
        $this->updatables = [
            'brand_id','name','unit_price','weight','spec','image'
        ];        
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['image', 'ruleImage', 'max'=>\Config::getBusinessSetting('limit_product_image')],
        ]);
    }   
    /**
     * Image validation rule
     * It accepts either a string url or array of urls
     */
    public function ruleImage($attribute, $params)
    {
        //coming from update, and image value no change
        if ($this->hasOldAttributes && \Helper::isInteger($this->image) && strcasecmp($this->oldAttributes['image'],$this->image)==0)
            return;
        
        $urlValidator = new \CUrlValidator();
        if (is_array($this->image)){
            
            if (count($this->image)>$params['max']){
                $this->addError('image',Sii::t('sii','Maximum {max} images are allowed.',array('{max}'=>$params['max'])));
                return;
            }
                
            foreach ($this->image as $key => $image) {
                if (!$urlValidator->validateValue($image))
                    $this->addError('image',Sii::t('sii','Image#{n} is not a valid URL.',array($key+1)));
            }
        }
        if (is_string($this->image))
            if (!$urlValidator->validateValue($this->image))
                $this->addError('image',Sii::t('sii','Image is not a valid URL.'));
    }
    /**
     * Serialize output for Api use
     * Support array of images output
     */
    public function toArray()
    {
        $result = $this->traitToArray();
        $images = [];
        foreach ($this->searchImages()->data as $image) {
            $images[] = $image->src_url;
        }
        $result['image'] = $images;
        return $result;
    }    
}
