<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use app\models\ApiModelTrait;
/**
 * Description of Subscription
 *
 * @author kwlok
 */
class Subscription extends \Subscription 
{
    use ApiModelTrait;  
    /**
     * @see SActiveRecord
     */ 
    public $enableUpdatableCheck  = true;  
    /**
     * Init class
     */
    public function init()
    {
        //Api output fields
        $this->fields = [
            'id','shop_id','account_id',
            'package'=>['type'=>'parent','referenceAttribute'=>'name','class'=>'app\modules\v1\models\Package'],
            'plan'=>['type'=>'parent','class'=>'app\modules\v1\models\Plan'],
            'start_date','end_date',
            'status'=>['type'=>'process'],
            'create_time'=>['type'=>'time'],
        ];
        //Insertable fields
        $this->insertables = [];        
        //Updatables fields
        $this->updatables = [];           
        //Api extra rules
        $this->extraRules = [];
    } 
}
