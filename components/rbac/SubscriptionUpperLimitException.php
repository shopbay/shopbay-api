<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use Sii;
use yii\web\HttpException;
/**
 * SubscriptionUpperLimitException represents an "validation" HTTP exception with status code 403
 *
 * Use the HttpException's $code to indicate the allowable limit
 *
 * @author kwlok
 */
class SubscriptionUpperLimitException extends HttpException
{
    /**
     * Constructor.
     * @param integer $limit The allowable limit
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($limit, \Exception $previous = null)
    {
        parent::__construct(403, Sii::t('sii','Upper limit exceeded'), $limit, $previous);
    }
}
