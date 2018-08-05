<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;
 
use \yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;
/**
 * Description of Config
 * 
 * @author kwlok
 */
class Config extends ActiveRecord implements Linkable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_config';
    }
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }
    /**
     * Define rules for validation
     */
    public function rules()
    {
        return [
            [['id', 'category', 'name','value'], 'required']
        ];
    }
    public function fields()
    {
        return ['id','name','value'];
    }
    
    public function extraFields()
    {
        return ['category'];
    }    
    
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['config/view', 'id' => $this->id], true),
        ];
    }    
}
