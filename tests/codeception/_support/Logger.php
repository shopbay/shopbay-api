<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\_support;

use Yii;
/**
 * Description of Logger
 *
 * @author kwlok
 */
class Logger extends \yii\base\BaseObject 
{
    public static $logPath;
    private $_t;   
    private $_log = '';   
    
    public function __construct($tester) 
    {
        $this->_t = $tester;
        Logger::$logPath = Yii::getAlias('@tests').'/codeception/_logs';
    }
    
    public function log($message)
    {
        $this->_log .= $message."\r\n";        
    }
    
    public function flush($file='test.log',$header=true)
    {
        if ($header){
            $this->_log = "<<< start ".date('m/d/Y h:i:s a')." >>>\r\n".$this->_log;   
        }
        $this->_t->writeToFile(Logger::$logPath.DIRECTORY_SEPARATOR.$file, $this->_log);
    }
}
