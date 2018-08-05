<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\models;

/**
 * Description of ApiModel
 *
 * @author kwlok
 */
class ApiModel extends \yii\base\BaseObject 
{
    const SCENARIO_CREATE = 'api-create';
    const SCENARIO_UPDATE = 'api-update';
    const SCENARIO_DELETE = 'api-delete';
    const SCENARIO_SUBSCRIBE = 'api-subscribe';
    const SCENARIO_UNSUBSCRIBE = 'api-unsubscribe';
    const SCENARIO_TRANSITION = 'api-transition';
}
