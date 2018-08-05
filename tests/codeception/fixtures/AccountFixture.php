<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of AccountFixture
 *
 * @author kwlok
 */
class AccountFixture extends ActiveFixture
{
    public $modelClass = 'tests\codeception\_support\models\Account';
    /**
     * Invoked before load()
     */
    public function beforeLoad() 
    {
        //delete child table
        //excludes in-built user id: 0, -1, 1
        //$this->db->createCommand()->delete('s_account_profile','id NOT IN (-1,0,1)')->execute();
        //$this->db->createCommand()->delete('s_activity','id NOT IN (-1,0,1)')->execute();
        parent::beforeLoad();
    }
}