<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of PlanItemFixture
 *
 * @author kwlok
 */
class PlanItemFixture extends ActiveFixture
{
    public $modelClass = 'app\modules\v1\models\PlanItem';
    public $depends = ['tests\codeception\fixtures\PlanFixture'];
}