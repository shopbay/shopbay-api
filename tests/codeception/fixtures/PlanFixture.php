<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of PlanFixture
 *
 * @author kwlok
 */
class PlanFixture extends ActiveFixture
{
    public $modelClass = 'app\modules\v1\models\Plan';
    /**
     * Invoked before load()
     */
    public function beforeLoad() 
    {
        //delete child table
        //it seems the Fixture $depends not working?
        $this->db->createCommand()->delete('s_plan_item')->execute();
        $this->db->createCommand()->delete('s_rbac_item')->execute();
        $this->db->createCommand()->delete('s_rbac_item_child')->execute();
        $this->db->createCommand()->delete('s_rbac_rule')->execute();
        $this->db->createCommand()->delete('s_rbac_assignment')->execute();
        $this->db->createCommand()->delete('s_subscription')->execute();
        parent::beforeLoad();
    }    
}