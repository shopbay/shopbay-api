<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of AccountProfileFixture
 *
 * @author kwlok
 */
class AccountProfileFixture extends ActiveFixture
{
    public $modelClass = 'tests\codeception\_support\models\AccountProfile';
    public $depends = ['tests\codeception\fixtures\AccountFixture'];
}