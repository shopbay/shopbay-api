<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\controllers;
 
use app\controllers\ResourceController;

/**
 * Description of ModelController
 *
 * @author kwlok
 */
abstract class ModelController extends ResourceController
{
    /**
     * @var string the name of module name. This property must be set if serviceManager are used.
     */    
    public $moduleName; 
    /**
     * @inheritdoc
     */   
    public function services()
    {
        return [
            'create'=>'create',
            'update'=>'update',
            'delete'=>'delete',
        ];
    }    
    /**
     * Dynamically load actions based on verbs() settings
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [];
        foreach (array_keys($this->verbs()) as $action) {
            $actions[$action] = [
                'class' => 'app\modules\v1\actions\Model'.ucfirst($action).'Action',
            ];
        }
        logTrace(__METHOD__.' actions loaded',$actions);
        return $actions;
    }    
    /**
     * @inheritdoc
     */
    protected function subscriptions()
    {
        return [];
    }
}