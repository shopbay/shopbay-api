<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
use yii\db\Schema;
use yii\db\Migration;
/**
 * Description of m150115_120518_table_create_user_unit_test_example
 * This file is to load test tables required for unit testing 
 * But, test tables are created for reference and they are not part of shopbay database schema. 
 * 
 * @author kwlok
 */
class m150115_120518_table_create_user_unit_test_example extends Migration
{
    public function up()
    {
        $this->createTable('user', [
                'id' => 'pk',
                'username' => 'varchar(24) NOT NULL',
                'password' => 'varchar(128) NOT NULL',
                'authkey' => 'varchar(255) NOT NULL',
            ]);

        $this->insert('user', [
                'username' => 'admin',
                'password' => Yii::$app->getSecurity()->generatePasswordHash('admin'),
                'authkey' => uniqid()
            ]);
        $this->insert('user', [
                'username' => 'demo',
                'password' => Yii::$app->getSecurity()->generatePasswordHash('demo'),
                'authkey' => uniqid()
            ]);
    }

    public function down()
    {
        $this->dropTable('user');
    }
}
