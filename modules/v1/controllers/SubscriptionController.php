<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;

use Sii;
use Yii;
use yii\web\UnauthorizedHttpException;
/**
 * Description of SubscriptionController
 *
 * @author kwlok
 */
class SubscriptionController extends ModelController
{
    /**
     * @var string the model class name. This property must be set.
     */    
    public $modelClass = 'app\modules\v1\models\Subscription';
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
        ];
    }     
    /**
     * @inheritdoc
     */   
    protected function permissions() 
    {
        return [
            'index'=>'Subscriptions.Management.Index',
            'view'=>'Subscriptions.Management.View',
            'check'=>'Subscriptions.Management.Check',
        ];
    }
    /**
     * @inhertdoc
     */
    protected function actionRules()
    {
        return array_merge(parent::actionRules(),[
            'check'=> ['rawBody'=>['null'=>false],'returnStatusCode'=>204],
        ]);
    }    
    /**
     * Action check implementation
     */
    public function actionCheck($subscription)
    {        
        logTrace(__METHOD__.' Receiving '.$subscription);
        return $this->process($this->action->id,null,function() use ($subscription) {
            return $this->checkSubscription($this->serviceUser, $subscription, $this->rawBody);
        });        
    }     
}
