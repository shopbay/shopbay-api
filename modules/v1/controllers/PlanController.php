<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;

use Sii;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
/**
 * Description of PlanController
 *
 * @author kwlok
 */
class PlanController extends ModelController
{
    /**
     * @var string the model class name. This property must be set.
     */    
    public $modelClass = 'app\modules\v1\models\Plan';
    /**
     * @var string the subscription model class name..
     */    
    public $subscriptionModelClass = 'app\modules\v1\models\Subscription';
    /**
     * @var string the name of module name. This property must be set.
     */    
    public $moduleName = 'plans'; 
    /**
     * @inheritdoc
     */   
    protected function permissions()
    {
        return [
            'index'=>'Plans.Management.Index',
            'create'=>'Plans.Management.create',
            'view'=>'Plans.Management.View',
            'update'=>'Plans.Management.Update',
            'delete'=>'Plans.Management.Delete',
            'submit'=>'Plans.Management.Submit',
            'approve'=>'Plans.Management.Approve',
            'subscribe'=>'Plans.Management.Subscribe',
            'unsubscribe'=>'Plans.Management.Unsubscribe',
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
            'subscribe'=> ['rawBody'=>['null'=>false]],
            'unsubscribe'=> ['rawBody'=>['null'=>true],'returnStatusCode'=>204],
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
            $returnModel = $this->getServiceManager($this->moduleName)->submit($this->serviceUser,$model);
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
            $planModel = $this->getServiceManager($this->moduleName)->approve($this->serviceUser,$model,$model->transition);
            //add plan as role and plan items as permission
            $this->subscriptionAuth->createRoleAndPermissions($planModel->name,$planModel->items);
            //flush cache
            $this->flushCache(\SCache::PUBLISHED_PACKAGES);
            //return promoted model
            return $this->promoteModel($this->modelClass, $planModel, false);
        });        
    } 
    /**
     * Action subscribe implementation
     * @param string $id The subscription no to be subscribed
     * @param string $subscriber Optional; If not set, system will assume subscriber is the login user (follow API HEADER AUTH)
     */
    public function actionSubscribe($id,$subscriber=null)
    {
        $planModel = $this->getViewApiModel($id);
        $planModel->setScenario(\app\models\ApiModel::SCENARIO_SUBSCRIBE);
        $planModel->prepareSubscription($this->rawBody);
        return $this->process($this->action->id,$planModel,function() use ($planModel, $subscriber) {
            $params = [];
            if ($subscriber==null){
                $subscriber = $this->serviceUser;
            }
            else {
                logInfo(__METHOD__.' Subscriber is '.$subscriber.' but login user is '.$this->serviceUser);
                //Not creating shop on this subscription mode ( done by login user)
                $params['createShop'] = false;
                $params['actionBy'] = $this->serviceUser;
            }
            
            $subscriptionModel = $this->getServiceManager($this->moduleName)->subscribe($subscriber,$planModel,$params);
            if ($subscriptionModel==false){
                throw new HttpException(500,Sii::t('sii','System is unable to perform subscription.'));
            }
            //return promoted model
            return $this->promoteModel($this->subscriptionModelClass, $subscriptionModel, false);
        });        
    }
    /**
     * Action usubscribe implementation
     * @param string $id The subscription no to be cancelled
     * @param string $subscriber Optional; If not set, system will assume subscriber is the login user (follow API HEADER AUTH)
     */
    public function actionUnsubscribe($id,$subscriber=null)
    {
        $model = $this->findSubscriptionModel($id,$subscriber);
        $model->setScenario(\app\models\ApiModel::SCENARIO_UNSUBSCRIBE);
        return $this->process($this->action->id,$model,function() use ($model, $subscriber) {
            $params = [];
            if ($subscriber==null){
                $subscriber = $this->serviceUser;
            }
            else {
                logInfo(__METHOD__.' Subscriber is '.$subscriber.' but login user is '.$this->serviceUser);
                //set checkAccess to false since unsubscription is done by login user (to avoid unauthorize access error)
                $params['checkAccess'] = false;
                $params['actionBy'] = $this->serviceUser;
            }
            $subscriptionModel = $this->getServiceManager($this->moduleName)->unsubscribe($subscriber,$model,$params);
            //return promoted model
            return $this->promoteModel($this->subscriptionModelClass, $subscriptionModel, false);
        });        
    }   
    /**
     * Action published implementation
     * This will retrieve all approved plans regardless of owner
     */
    public function actionPublished()
    {        
        $dataProvider = $this->getDataProvider('approved');
        return $this->process($this->action->id,$dataProvider,function() use ($dataProvider) {
            return $dataProvider;
        });        
    }      
    /**
     * Get subscription model for api use
     * @param string $id The subscription no
     * @param string $user Optional; The target user to unsubscribe for. If not set, system will assume user is the login user (follow API HEADER AUTH)
     */
    protected function findSubscriptionModel($id,$user=null)
    {
        //check which user to find subscription for
        if ($user==null)
            $user = Yii::$app->user->id;
        //only active subscription will be selected
        $model = \Subscription::model()->mine($user)->subscriptionNo($id)->active()->find();
        if ($model==null)
            throw new NotFoundHttpException(Sii::t('sii','You have not subscribed to this plan: {id}',['{id}'=>$id]));
        else{
            try {
                $modelClass = $this->subscriptionModelClass;
                $apiModel = new $modelClass();      
                $oldAttributes = $apiModel->prepareOldAttributes($model);
                $apiModel->prepareUpdate($this->rawBody,$model->attributes,$oldAttributes);
                return $apiModel;                
            } catch (\CException $ex) {
                logError(__METHOD__,$ex->getTraceAsString());
                throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::UPDATE_ERROR);
            }            
        }
    }    
    
}
