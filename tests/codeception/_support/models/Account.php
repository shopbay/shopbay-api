<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\_support\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "s_account".
 *
 * @property integer $id
 * @property string  $username
 * @property string  $password
 * @property string  $authkey
 */
class Account extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_account';
    }
    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function fields()
    {
        $fields = parent::fields();

        if (Yii::$app->user->getId() != $this->getId()) {
            unset($fields['password'], $fields['access_token']);
        }

        return $fields;
    }
    /**
     * Setter for the password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException(__METHOD__ . ' has not been implemented.');
    }
    /**
     * Finds user by username
     *
     * @param  string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::findOne(['name' => $username]);
    }
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authkey;
    }
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    /**
     * Validates password
     *
     * @param  string $password password to validate
     *
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword(
            $password,
            $this->password
        );
    }    

}
