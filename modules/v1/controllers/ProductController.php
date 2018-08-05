<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;

use Feature;
/**
 * Description of ProductController
 *
 * @author kwlok
 */
class ProductController extends ModelController
{
    /**
     * @var string the model class name. This property must be set.
     */    
    public $modelClass = 'app\modules\v1\models\Product';
    /**
     * @var string the name of module name. This property must be set.
     */    
    public $moduleName = 'products';  
    /**
     * @inheritdoc
     */   
    public function services()
    {
        return [
            'create'=>'createProduct',
            'update'=>'updateProduct',
            'delete'=>'deleteProduct',
        ];
    }         
    /**
     * @inheritdoc
     */   
    protected function permissions()
    {
        return [
            'index'=>'Products.Management.Index',
            'create'=>'Products.Management.Create',
            'view'=>'Products.Management.View',
            'update'=>'Products.Management.Update',
            'delete'=>'Products.Management.Delete',
        ];
    }     
    /**
     * This is the protection at Api level when calling via following service actions
     * @inheritdoc
     */ 
    protected function subscriptions()
    {
        return [
            'create'=> Feature::patternize(Feature::$hasProductLimitTierN),
        ];
    }    
}
