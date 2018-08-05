<?php
namespace app\tests\codeception\unit\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'tests\codeception\unit\models\User';
}