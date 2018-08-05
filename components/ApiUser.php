<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * This class is needed to authenticate Yii::app() - Yii1 app
 * 
 * @see bootstrapYii1Engine() for its loading
 * @author kwlok
 */
class ApiUser extends WebUser 
{
    /**
     * @var boolean If to store avatar in session
     * No needed in api app as no proper asset directory structure is in place
     */
    public $storeSessionAvatar = false;
}
