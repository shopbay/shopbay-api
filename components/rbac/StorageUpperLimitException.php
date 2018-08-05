<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace app\components\rbac;

use yii\web\HttpException;
/**
 * StorageUpperLimitException represents an "validation" HTTP exception with status code 403
 *
 * Use the HttpException's $code to indicate the allowable limit
 *
 * @author kwlok
 */
class StorageUpperLimitException extends HttpException
{
    /**
     * Constructor.
     * @param string $message The exception message 
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct(403, $message, 403, $previous);
    }
}
