<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use yii\web\HttpException;
/**
 * SubscriptionException represents an "validation" HTTP exception with message entailing which service request is rejected
 *
 * Use the HttpException's $message to indicate the rejected service name
 *
 * @author kwlok
 */
class SubscriptionException extends HttpException
{
    /**
     * Constructor.
     * @param string $service The allowable limit
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($service, \Exception $previous = null)
    {
        parent::__construct(401, $service, 0, $previous);
    }
}
