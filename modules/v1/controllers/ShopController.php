<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;
 
use Feature;
/**
 * Description of ShopController
 *
 * @author kwlok
 */
class ShopController extends ModelController
{
    /**
     * @var string the model class name. This property must be set.
     */    
    public $modelClass = 'app\modules\v1\models\Shop';
    /**
     * @var string the name of module name. This property must be set.
     */    
    public $moduleName = 'shops'; 
    /**
     * @inheritdoc
     */   
    public function services()
    {
        return [
            'create'=>'create',
            'update'=>'update',
            'delete'=>'delete',
            'apply'=>'apply',
        ];
    }         
    /**
     * @inheritdoc
     */   
    protected function permissions()
    {
        return [
            'index'=>'Shops.Management.Index',
            'create'=>'Shops.Management.Create',
            'view'=>'Shops.Management.View',
            'update'=>'Shops.Management.Update',
            'delete'=>'Shops.Management.Delete',
            'apply'=>'Shops.Management.Apply',
        ];
    }
    /**
     * @inhertdoc
     */
    protected function actionRules()
    {
        return array_merge(parent::actionRules(),[
            'apply'=>['rawBody'=>['null'=>false]],
        ]);
    }        
    /**
     * This is the protection at Api level when calling via following service actions
     * @inheritdoc
     */ 
    protected function subscriptions()
    {
        return [
            'create'=> Feature::patternize(Feature::$hasShopLimitTierN),
            //TODO apply shop should make chargeable, e.g. $x per shop, and not tied to tier limit
            'apply'=> Feature::patternize(Feature::$hasShopLimitTierN),
        ];
    }
    /**
     * Action apply shop implementation
     */
    public function actionApply()
    {        
        $model = $this->getCreateApiModel();
        return $this->process($this->action->id,$model,function() use ($model) {
            return $this->getServiceManager($this->moduleName)->{$this->services()[$this->action->id]}($this->serviceUser,$model);
        });    
    }    
}
