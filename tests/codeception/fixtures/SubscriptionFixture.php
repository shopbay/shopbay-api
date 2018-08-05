<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of SubscriptionFixture
 *
 * @author kwlok
 */
class SubscriptionFixture extends ActiveFixture
{
    public $modelClass = 'app\modules\v1\models\Subscription';
    /**
     * Invoked before load()
     */
    public function beforeLoad() 
    {
        //delete child table
        $this->db->createCommand()->delete('s_subscription_plan')->execute();
        parent::beforeLoad();
    }
    
}