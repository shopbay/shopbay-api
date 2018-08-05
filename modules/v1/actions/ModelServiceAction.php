<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\actions;

/**
 * Description of ModelServiceAction
 *
 * @author kwlok
 */
abstract class ModelServiceAction extends \yii\base\Action
{
    /**
     * A boilerplate to invoke service
     * @param type $model
     * @return type
     */
    public function invokeService($model)
    {
        return $this->controller->process($this->id,$model,function() use ($model) {
            return $this->controller->getServiceManager($this->controller->moduleName)->{$this->controller->services()[$this->id]}($this->controller->serviceUser,$model);
        });        
        
    }
}
