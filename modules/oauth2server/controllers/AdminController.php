<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server\controllers;

/**
 * Description of AdminController
 *
 * @author kwlok
 */
class AdminController extends LoginController 
{
    protected $identityClass = '\IdentityAdmin';
}
