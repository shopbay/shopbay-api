<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use app\models\ApiModelTrait;
/**
 * Description of Transition
 *
 * @author kwlok
 */
class Transition extends \Transition 
{
    use ApiModelTrait;  
        /**
     * @see SActiveRecord
     */
    public $enableInsertableCheck = true;   
    /**
     * Init class
     */
    public function init()
    {
        //Api output fields
        $this->fields = ['action','decision','condition1','condition2'];
        //Insertable fields
        $this->insertables = ['decision','condition1','condition2'];
    }      
    
}
