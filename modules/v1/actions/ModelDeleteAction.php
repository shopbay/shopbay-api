<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\actions;

/**
 * Description of ModelDeleteAction
 *
 * @author kwlok
 */
class ModelDeleteAction extends ModelServiceAction
{
    /**
     * @param string $id the primary key of the model.
     * @return ApiModel
     */
    public function run($id)
    {
        $model = $this->controller->getDeleteApiModel($id);
        return $this->invokeService($model);
    }     
}
