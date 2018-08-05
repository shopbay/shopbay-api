<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
use tests\codeception\_migrations\Migration;
/**
 * Description of m150115_110000_init
 * 
 * @author kwlok
 */
class m150115_110000_init extends Migration
{
    public function up()
    {
        //do nothing
        //can load full database schema here
        //e.g. $this->loadSchema('common.path.alias','install.sql');        
        //Right now use Console command DatabaseCommand::schema()
    }

    public function down()
    {
        echo "m150115_110000_init does not support migration down.\n";
        return false;
    }
    
}
