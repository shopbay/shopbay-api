<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\actions;

/**
 * Description of ModelIndexAction
 *
 * @author kwlok
 */
class ModelIndexAction extends \yii\base\Action
{
    public $modelFilter = 'mine';
    /**
     * @return ActiveDataProvider
     */
    public function run()
    {
        $dataProvider = $this->controller->getDataProvider($this->modelFilter,$this->controller->serviceUser);
        return $this->controller->process($this->id,$dataProvider,function() use ($dataProvider) {
            return $dataProvider;
        });        
    }    
}
