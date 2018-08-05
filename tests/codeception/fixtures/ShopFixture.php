<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;
/**
 * Description of ShopFixture
 *
 * @author kwlok
 */
class ShopFixture extends ActiveFixture
{
    public $modelClass = 'app\modules\v1\models\Shop';
}