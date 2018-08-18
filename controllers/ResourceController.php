<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\controllers;

use Sii;
use Yii;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use app\models\ApiModel;
use app\modules\v1\models\Shop;
Yii::import('common.services.exceptions.*');
use \ServiceException;
use app\components\rbac\SubscriptionRbacManager;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use app\modules\oauth2server\filters\auth\ResourceAuth;

/**
 * Description of ResourceController
 *
 * @author kwlok
 */
abstract class ResourceController extends Controller
{
    /**
     * The llist of available service manager to be loaded for backend operations
     * @var array 
     */
    public static $serviceManager = [];
    /**
     * @var string the model class name. This property must be set.
     */
    public $modelClass;
    /**
     * @var string the raw body send by client
     */
    public $rawBody;  
    /**
     * The user id that will be passed into ServiceManager. 
     * It is captured at HTTP custom header X-Request-For
     * @var type 
     */
    public $serviceUser;
    /**
     * The percord per page for data provider
     * @var type 
     */
    public $recordPerPage;
    /**
     * Serializer definition
     * @var type 
     */
    public $serializer = [
        'class' => 'app\modules\v1\components\Serializer',
        'collectionEnvelope' => 'items',
    ];
    /**
     * Subscription rbac manager
     * @var type 
     */
    public $subscriptionAuth;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        $this->subscriptionAuth = new SubscriptionRbacManager();
        
        if (!isset($this->recordPerPage))
            $this->recordPerPage = \Config::getSystemSetting('record_per_page');
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => ResourceAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    //['class' => QueryParamAuth::className(), 'tokenParam' => 'access_token'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }    
    /**
     * Declares the allowed HTTP verbs.
     * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
     * @return array the allowed HTTP verbs.
     */
    protected function verbs()
    {
        return [
            'index'  => ['get'],
            'view'   => ['get'],
            'create' => ['post'],
            'update' => ['put', 'patch'],
            'delete' => ['delete'],
        ];
    }
    /**
     * Declares the action rules 
     * keys: 'checkOwnObject', 'rawBody', 'returnStatusCode'
     * @return array the rule configuration.
     */
    protected function actionRules()
    {
        return [
            'index'=> [],
            'create'=>['rawBody'=>['null'=>false]],
            'view' => ['checkOwnObject'=>true],
            'update'=>['checkOwnObject'=>true,'rawBody'=>['null'=>false]],
            'delete'=>['checkOwnObject'=>true,'rawBody'=>['null'=>true],'returnStatusCode'=>204],
        ];
    }    
    /**
     * Permissions binding to declare the allowed verbs
     * Formart: ['<verb>'=> '<permission>',..]
     * @return array the rule configuration.
     */
    abstract protected function permissions();
    /**
     * Subscriptions binding to declare the allowed verbs
     * Formart: ['<verb>'=> '<subscription>',..]
     * @return array the rule configuration.
     */
    abstract protected function subscriptions();    
    /**
     * Before action
     * @param type $action
     * @throws BadRequestHttpException
     */
    public function beforeAction($action) 
    {
        logTrace(__METHOD__." Action $action->id: raw body received",Yii::$app->getRequest()->getRawBody());            
        $this->rawBody = json_decode(Yii::$app->getRequest()->getRawBody(),true);        
        logTrace(__METHOD__." Action $action->id: Json decode raw body",$this->rawBody);    
        foreach ($this->actionRules() as $key => $rule) {
            if ($key==$action->id && isset($rule['rawBody']) && !$rule['rawBody']['null'] && $this->rawBody===null){
                logError(__METHOD__." Bad input data! Action $action->id requires non-null raw body request.",$this->rawBody);
                throw new BadRequestHttpException(Sii::t('sii','Bad input data. Raw body in request is null.'));
            }
            elseif ($key==$action->id && isset($rule['rawBody']) && $rule['rawBody']['null'] && $this->rawBody!=null){
                logError(__METHOD__." Bad input data! Action $action->id requires null raw body request.",$this->rawBody);
                throw new BadRequestHttpException(Sii::t('sii','Bad input data. Raw body in request is not null.'));
            }
        }
        return parent::beforeAction($action);
    }
    /**
     * After action
     * @param type $action
     * @throws BadRequestHttpException
     */
    public function afterAction($action, $result)
    {
        //this may not be called as API controller is run using cookieless session;
        //calling it just to put extra security measure
        logTrace(__METHOD__." $action->id: logout ".(!Yii::app()->user->isGuest?'login user':'guest').'.');            
        Yii::app()->user->logout();    
        return parent::afterAction($action, $result);
    }
    /**
     * A process template 
     * @param string $action the ID of the action to be executed
     * @param Closure $logic
     * @param array $params additional parameters
     * @return type
     */
    public function process($action,$model,$logic, $params=[]) 
    {
        try {            
            $this->checkAccess($action,$model,$params);//inside serviceManager there is second level permission check

            if (($statusCode = $this->searchReturnStatusCode($action))!=false)
                Yii::$app->getResponse()->setStatusCode($statusCode);      
            
            return call_user_func($logic);
            
        } catch (\CException $ex) {
            logError(__METHOD__,$ex->getTraceAsString());
            return $ex;
        }
    }    
    /**
     * Checks the privilege of the current user. (Authorization)
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        //Below two fields will be null when run in cookie-less session?
        //Ideally both should match, except for System user since no authentication for Yii1 app.
        logInfo(__METHOD__.' Yii 1 user id', Yii::app()->user->id);//if login user is system, user id = null
        logInfo(__METHOD__.' Yii 2 user id', Yii::$app->user->id);
        logInfo(__METHOD__.' Yii 2 user identity', Yii::$app->user->identity);
        if (!isset(Yii::$app->user->id)){
            throw new UnauthorizedHttpException(Sii::t('sii','Unauthorized service user'));
        }
        
        //Set correct service user to run checks
        $this->setServiceUser();
        
        $pass = false;
        foreach ($this->actionRules() as $key => $rule) {
            //first check if user has access for the action
            if ($key==$action && isset($this->permissions()[$action])){
                if ($this->checkPermission($this->permissions()[$action]))
                    $pass = true;
                else
                    throw new UnauthorizedHttpException(Sii::t('sii','No Permission to access action {action}.',['{action}'=>$action]));
            }
            //second check if user has subscribed to the service
            if ($key==$action && isset($this->subscriptions()[$action])){
                $service = $this->subscriptions()[$action];
                $pass = $this->checkSubscription($this->serviceUser, $service);
            }
            //thirdly, check if user is accesing own object
            if ($key==$action && isset($rule['ownObject']) && $rule['ownObject']==true){
                if ($this->serviceManager->checkObjectAccess($this->serviceUser,$model)){
                    logInfo(__METHOD__.' user '.$this->serviceUser.' for '.$action.' ok');
                    $pass = true;
                }
            }
            
            //fourthly, check if request is to skip permission check
            if ($key==$action && isset($rule['skipPermissions']) && $rule['skipPermissions']==true){
                logInfo(__METHOD__.' user '.$this->serviceUser.' for '.$action.' ok');
                $pass = true;
            }
        }
        
        if ($pass)
            return true;
        else
            throw new UnauthorizedHttpException(Sii::t('sii','Unauthorized access to {action}.',['{action}'=>$action]));
    }   
    /**
     * Get dataprovider based on $modelClass
     * @param type $modelFilter
     * @param type $user
     * @return \CActiveDataProvider
     * @throws \ServiceOperationException
     */
    public function getDataProvider($modelFilter,$user=null)
    {
        try {
            $modelClass = $this->modelClass;
            $finder = $modelClass::model()->{$modelFilter}($user)->all();
            logTrace(__METHOD__.' Criteria',$finder->getDbCriteria());
            $dataProvider = new \CActiveDataProvider($finder, [
                            'criteria'=>['order'=>'id ASC'],
                            'pagination'=>[
                                'pageVar'=>'page',
                                'pageSize'=>isset($_GET['per-page'])?$_GET['per-page']:$this->recordPerPage,
                                'currentPage'=>isset($_GET['page'])?$_GET['page']-1:0,
                            ],
                            'sort'=>false,
                        ]);
            //re-assign data to serialized models
            $models = [];
            foreach ($dataProvider->data as $model) 
                $models[] = $this->promoteModel($modelClass, $model);
            $dataProvider->data = $models;
            return $dataProvider;
        
        } catch (\CException $ex) {
            logError(__METHOD__,$ex->getTraceAsString());
            throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::READ_ERROR);
        }
    }    
    /**
     * Create instance of api model based on $modelClass
     */
    public function getCreateApiModel()
    {
        try {
            $modelClass = $this->modelClass;
            $apiModel = new $modelClass();
            $apiModel->prepareCreate($this->rawBody);
            return $apiModel;
        } catch (\CException $ex) {
            logError(__METHOD__,$ex->getTraceAsString());
            throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::CREATE_ERROR);
        }
    }
    /**
     * This is a special find source model for api use..
     * It first find the Yii1 model , and next return corresponding api model based on $this->modelClass.
     * 
     * @param type $id
     * @return CActiveRecord
     * @throws \CException
     */
    public function getViewApiModel($id)
    {
        $modelClass = $this->modelClass;
        if ($id !== null) {
            $model = $modelClass::model()->findByPk($id);
        }        
        if (isset($model)) {
            try {
                return $this->promoteModel($modelClass, $model, false);
            } catch (\CException $ex) {
                logError(__METHOD__,$ex->getTraceAsString());
                throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::READ_ERROR);
            }
        } else {
            throw new NotFoundHttpException(Sii::t('sii','Object not found: {id}',['{id}'=>$id]));
        }
    }    
    /**
     * This is a special find source model for api use..
     * It first find the Yii1 model , and next return corresponding api model based on $this->modelClass.
     * 
     * @param type $id
     * @return CActiveRecord
     * @throws \CException
     */
    public function getUpdateApiModel($id)
    {
        $modelClass = $this->modelClass;
        if ($id !== null) {
            $model = $modelClass::model()->findByPk($id);
        }        
        if (isset($model)) {
            try {
                $apiModel = new $modelClass();      
                $oldAttributes = $apiModel->prepareOldAttributes($model);
                $apiModel->prepareUpdate($this->rawBody,$model->attributes,$oldAttributes);
                return $apiModel;
            } catch (\CException $ex) {
                logError(__METHOD__,$ex->getTraceAsString());
                throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::UPDATE_ERROR);
            }
            
        } else {
            throw new NotFoundHttpException(Sii::t('sii','Object not found: {id}',['{id}'=>$id]));
        }
    }
    /**
     * Get api model for deleting use
     * @param type $id
     * @return type
     * @throws \ServiceOperationException
     */
    public function getDeleteApiModel($id)
    {
        try {
            $model = $this->getUpdateApiModel($id);
            $model->setScenario(ApiModel::SCENARIO_DELETE);
            return $model;
        } catch (\CException $ex) {
            logError(__METHOD__,$ex->getTraceAsString());
            throw new \ServiceOperationException(Sii::t('sii','API model error.'),ServiceException::DELETE_ERROR);
        }     
    }
    /**
     * This is a special find source model for api use..
     * It first find the Yii1 model , and next return corresponding api model based on $this->modelClass.
     * 
     * @param type $id
     * @return CActiveRecord
     * @throws \CException
     */
    public function getTransitionApiModel($id,$action)
    {
        $modelClass = $this->modelClass;
        if ($id !== null) {
            $model = $modelClass::model()->findByPk($id);
        }        
        if (isset($model)) {
            try {
                $apiModel = new $modelClass();
                $oldAttributes = $apiModel->prepareOldAttributes($model);
                $apiModel->prepareTransition($action,$this->rawBody,$model->attributes,$oldAttributes);
                return $apiModel;
            } catch (\CException $ex) {
                logError(__METHOD__,$ex->getTraceAsString());
                throw new \ServiceOperationException(Sii::t('sii','API error.'),ServiceException::TRANSITION_ERROR);
            }
            
        } else {
            throw new NotFoundHttpException(Sii::t('sii','Object not found: {id}',['{id}'=>$id]));
        }
    }          
    /**
     * Retrieve service manager for processing
     * @param type $module
     * @return type
     */
    public function getServiceManager($module=null)
    {
        if (!isset($module)){
            $serviceManager = Yii::app()->serviceManager;
            $serviceManager->runMode = 'api';
            return $serviceManager;
        }
        
        if (!isset(self::$serviceManager[$module])){
            $serviceManager = Yii::app()->getModule($module)->serviceManager;
            $serviceManager->runMode = 'api';
            self::$serviceManager[$module] = $serviceManager;
        }
        
        return self::$serviceManager[$module];
    }    
    /**
     * Promote model to make it suitable for api use
     * @param type $modelClass
     * @param type $model
     * @return type
     */
    protected function promoteModel($modelClass,$model,$returnArray=true) 
    {
        $apiModel = Yii::configure(new $modelClass(), $model->attributes);
        return $apiModel->prepareChilds($model,$returnArray);
    }
    /**
     * Check user permission
     * @param type $permission
     * @return boolean
     */
    protected function checkPermission($permission) 
    {
        logTrace(__METHOD__.' Check '.$permission.' for user '.$this->serviceUser.' ...');
        if (Yii::app()->getAuthManager()->checkAccess($permission,$this->serviceUser)){
            logInfo(__METHOD__.' Check '.$permission.' for user '.$this->serviceUser.' ok');
            return true;
        }
        else
            return false;
    }
    /**
     * Search if to return specific status code for action
     * @param type $action
     * @return mixed Status code or fasle if not found
     */
    protected function searchReturnStatusCode($action)
    {
        foreach ($this->actionRules() as $key => $rule) {        
            if ($key==$action && isset($rule['returnStatusCode'])){
                return $rule['returnStatusCode'];       
            }
        }       
        return false;
    }
    /**
     * Flush common cache by key in 
     * @param type $key
     */
    protected function flushCache($key)
    {
        Yii::app()->commonCache->delete($key);
        logInfo(__METHOD__.' '.$key.' ok');
    }
    /**
     * Check if user has access according to subscription
     * @param type $user
     * @param type $service
     * @param type $params
     * @return boolean
     * @throws UnauthorizedHttpException
     */
    protected function checkSubscription($user,$service,$params=[])
    {
        logTrace(__METHOD__." Service $service params",$params);
        $subscriptions = $this->findSubscriptions($user);
        $params = array_merge(['subscriptions'=>$subscriptions],$params);
        $service = $this->findSubscriptionService($subscriptions, $service, $params);
        logTrace(__METHOD__." Checking service '$service'...");
        
        //First assign current app user to $sessionUser
        $sessionUser = Yii::$app->user;
        
        if (get_class(Yii::$app->user->identity)=='app\models\CustomerUser') {
            //if current user is CustomerUser, change the session user to shop owner
            //As customer user subscription checks is based on shop owners' granted permissions
            $sessionUser = new \yii\web\User([
                'identityClass' => 'IdentityUser',
            ]);
            $sessionUser->setIdentity(new \app\models\User([
                'id'=>$user,
                'username' => $user,
                'identityType' => 'IdentityUser',
                'roles' => null,
                'activate' => false,                
            ]));
            logTrace(__METHOD__." Swap current app user to '$user'...");
        }

        if ($sessionUser->can($service,$params)){
            logInfo(__METHOD__.' User '.$sessionUser->id.' has subscription service '.$service);
            return true;
        }                    
        else {
            logError(__METHOD__.' User '.$sessionUser->id.' has no subscription to service: '.$service);
            throw new UnauthorizedHttpException(Sii::t('sii','You have no subscription to this service.'));
        }
        
    }
    /**
     * This finds all the subscriptions user has (user can have many!)
     * @param type $user
     * @return array
     */
    protected function findSubscriptions($user) 
    {
        $models = \Subscription::model()->myPlans($user)->active()->notExpired()->findAll();
        if ($models==null || empty($models)){
            return [];
        }   
        return $models;
    }
    /**
     * Find the subscription service full name 
     * @param type $subscriptions
     * @param type $service
     * @param type $params
     * @return type
     */
    protected function findSubscriptionService($subscriptions,$service,$params)
    {
        //1. When shop is known 
        if (isset($params['shop'])){
            foreach ($subscriptions as $sub) {
                if ($sub->shop_id==$params['shop']){//matching shop subscription
                    $service = \SubscriptionPermission::model()->fuzzySearch($sub->plan->name,$service);
                    logInfo(__METHOD__.' Service found by fuzzy search for shop '.$params['shop'],$service);
                    break;
                }
            }                    
        }
        //2. When shop is unknown - take the first subscription found
        else {
            foreach ($subscriptions as $sub) {
                $service = \SubscriptionPermission::model()->fuzzySearch($sub->plan->name,$service);
                logInfo(__METHOD__.' Service found by fuzzy search',$service);
                break;//take the first found item
            }                    
        }
        return $service;
    }
    /**
     * Set the correct service user to run permission checks
     */
    protected function setServiceUser()
    {
        $userClass = get_class(Yii::$app->user->identity);
        switch ($userClass) {
            case 'app\models\CustomerUser':
                $shop = Shop::model()->findByPk(Yii::$app->user->identity->shop);
                //When Shop is found, and service is granted
                if ($shop!=null){
                    //Change service user to shop owner. 
                    //Customer account is following 
                    $this->serviceUser = $shop->account_id;
                    logInfo(__METHOD__.' Change service user to shop owner '.$this->serviceUser.' on behalf of customer '.Yii::$app->user->id);
                }
                break;
            case 'app\models\MerchantUser':
                //@todo
                break;
            default://for 'Account'
                //default service user follows the detected Yii::$app->user (the user who initiates api request)
                $this->serviceUser = Yii::$app->user->id;
                logInfo(__METHOD__.' User id',$this->serviceUser);
                break;
        }//end switch        
        
    }
}
