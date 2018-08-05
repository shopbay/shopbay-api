<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\oauth2server;

use Yii;
/**
 * Fork of \filsh\yii2\oauth2server\Module
 *
 * @author kwlok
 */
class Module extends \filsh\yii2\oauth2server\Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->autoloadBshafferOAuth2Server();
        parent::init();
    }    
    
    protected function autoloadBshafferOAuth2Server() 
    {
        require_once(__DIR__.'/vendor/bshaffer/oauth2-server-php/src/OAuth2/Autoloader.php');
        \OAuth2\Autoloader::register();
    }
    /**
     * OVERRIDDEN
     * 
     * This method is overriden to avoid error caused when using Yii 2.0.13 or above
     * -> Argument 1 passed to OAuth2\\Server::handleTokenRequest() must be an instance of OAuth2\\RequestInterface, instance of yii\\web\\Request given, ...
     * 
     * @return type
     */
    public function getRequest()//seems not working even using back old version 2.0.6
    {
        $this->set('request', \OAuth2\Request::createFromGlobals());
        return $this->get('request');
        //$request = parent::getRequest();
        //        if (!$request instanceof OAuth2\RequestInterface){
        //            $this->set('request', Request::createFromGlobals());
        //            return $this->get('request');
        //        }
        //        else
        //            return $request;
    }
    
}
