<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;

use Sii;
use Yii;
use yii\web\NotFoundHttpException;
/**
 * Description of PackageController
 *
 * @author kwlok
 */
class PackageController extends ModelController
{
    /**
     * @var string the model class name. This property must be set.
     */    
    public $modelClass = 'app\modules\v1\models\Package';
    /**
     * @var string the name of module name. This property must be set.
     */    
    public $moduleName = 'plans'; 
    /**
     * @inheritdoc
     */   
    public function services()
    {
        return [
            'create'=>'createPackage',
            'update'=>'updatePackage',
            'delete'=>'deletePackage',
        ];
    }      
    /**
     * @inheritdoc
     */   
    protected function permissions()
    {
        return [
            'index'=>'Plans.Package.Index',
            'create'=>'Plans.Package.create',
            'view'=>'Plans.Package.View',
            'update'=>'Plans.Package.Update',
            'delete'=>'Plans.Package.Delete',
            'submit'=>'Plans.Package.Submit',
            'approve'=>'Plans.Package.Approve',
        ];
    }
    /**
     * @inhertdoc
     */
    protected function actionRules()
    {
        return array_merge(parent::actionRules(),[
            'submit'=> ['rawBody'=>['null'=>true]],
            'approve'=>['rawBody'=>['null'=>false]],
            'published'=> ['skipPermissions'=>true,'rawBody'=>['null'=>true]],
        ]);
    }    
    /**
     * Action submit implementation
     */
    public function actionSubmit($id)
    {        
        $model = $this->getTransitionApiModel($id,$this->action->id);
        return $this->process($this->action->id,$model,function() use ($model) {
            $returnModel = $this->getServiceManager($this->moduleName)->submitPackage($this->serviceUser,$model);
            return $this->promoteModel($this->modelClass, $returnModel, false);
        });        
    } 
    /**
     * Action approve implementation
     */
    public function actionApprove($id)
    {        
        $model = $this->getTransitionApiModel($id,$this->action->id);
        return $this->process($this->action->id,$model,function() use ($model) {
            $planModel = $this->getServiceManager($this->moduleName)->approvePackage($this->serviceUser,$model,$model->transition);
            //flush cache
            $this->flushCache(\SCache::PUBLISHED_PACKAGES);
            //return promoted model
            return $this->promoteModel($this->modelClass, $planModel, false);
        });        
    } 
    /**
     * Action published implementation
     * This will retrieve all published packages regardless of owner
     */
    public function actionPublished()
    {        
        $dataProvider = $this->getDataProvider('published');
        return $this->process($this->action->id,$dataProvider,function() use ($dataProvider) {
            return $dataProvider;
        });        
    } 
    
}
