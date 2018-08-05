<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Below are the Yii1 aliases and import setup, and also start a Yii1 engine application
 * This is currently used by Yii2 based app - shopbay-api
 * 
 * @author kwlok
 * 
 * @param string $app the app to own the Yii1 app
 * @param boolean $testEnv indicate if is run in test environment
 */
function bootstrapYii1Engine($app,$testEnv=false)
{
    Yii::setPathOfAlias('common', KERNEL);
    Yii::setPathOfAlias('console', ROOT.'/shopbay-console');
    Yii::setPathOfAlias($app, ROOT.'/'.$app);
    Yii::import('common.components.*');
    Yii::import($app.'.components.*');
    $basepath = dirname(__FILE__).DIRECTORY_SEPARATOR.'../../'.$app;
    $webapp = new SWebApp($app,$basepath);
    $webapp->import([
        'common.components.*',
        'common.models.*',
        'common.services.*',
        'common.modules.accounts.users.*',
    ]);
    $webapp->serviceRunMode = 'api';
    $config = $webapp->toArray();
    //For Yii2-based, the www root folder is 'web' and not 'www' (as in Yii 1)
    //Change image component $baseRelativePath 
    $config['components']['image']['baseRelativePath'] = $config['id'].'/web';
    
    if ($testEnv==true){//get test db
        $testConfig = include ROOT.DIRECTORY_SEPARATOR.$app.'/tests/codeception/config/config.php';
        $testdb = $testConfig['components']['db'];
        $config['components']['db']['connectionString'] = $testdb['dsn'];
        $config['components']['db']['username'] = $testdb['username'];
        $config['components']['db']['password'] = $testdb['password'];
        $config['modules']['billings']['paymentGateway'] = 'common.modules.payments.components.TestPaymentGateway';
        $config['components']['image']['modelClass'] = 'Image';//switch to Image as default, not using MediaAssociation
    }
    Yii::createApplication('CWebApplication',[
        'id'=> $config['id'],
        'basePath'=> $config['basePath'],//dummy basepath, but pointing to $app basepath
        'name'=>$config['name'],
        'import'=>$config['import'],
        'modules'=> $config['modules'],
        'params'=>$config['params'],
        'components'=>[
            'cache'=> $config['components']['cache'],
            'commonCache'=> $config['components']['commonCache'],
            'db'=> $config['components']['db'],
            'authManager'=> $config['components']['authManager'],
            'serviceManager'=>$config['components']['serviceManager'],
            'assetManager'=>$config['components']['assetManager'],
            'image'=> $config['components']['image'],
            'user'=> [
                'class'=> 'ApiUser',
                'allowAutoLogin'=> false,//disable cookie-based login
                'loginUrl'=>null,
            ],
            'session' => [
                'class' => 'SHttpSession',
            ],
            'request' => [
                'class' => 'common.components.SHttpRequest',
                'enableCsrfValidation' => false,//disable for yii1 app <- codeception test
                'enableCookieValidation'=> false,
                'csrfTokenName'=>$webapp->params['CSRF_TOKEN_NAME'],
            ],                    
            'urlManager'=> [
                'class'=>'SUrlManager',
                'hostDomain'=>$webapp->params['HOST_DOMAIN'],
                'merchantDomain'=>$webapp->params['MERCHANT_DOMAIN'],
            ],
        ],
    ]);
}