<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\_support;

use Yii;
/**
 * Description of TestHelper
 *
 * @author kwlok
 */
class TestHelper extends \yii\base\BaseObject 
{
    private $_t;//tester   
    private $_f;//fixtures   
    private $_m;//model name   
    public static $logger;//logger   
    
    public function __construct($tester,$model) 
    {
        $this->_t = $tester;
        $this->_m = $model;
        $this->_f = $this->_t->getFixture($this->_m);
        if (self::$logger===null){
            self::$logger = new Logger($this->_t);   
        }
    }
    
    public function getFixtures()
    {
        return $this->_f;      
    }
    
    public function getTester()
    {
        return $this->_t;      
    }
    
    public function getData($key)
    {
        return $this->getRow($key);      
    }    
    
    public function getRow($key)
    {
        return $this->_f[$key];      
    }
    
    public function getLogger()
    {
        return $this->_l;      
    }  
    
    public function log($message)
    {
        self::$logger->log($message);
    }
    
    public function flushLog($file='test.log',$header=true)
    {
        if ($header)
            $this->log('*** '.$this->_m.' ***');
        self::$logger->flush($file,$header);
    }    

    public static function readFile($filename,$filepath=null)
    {
        if ($filepath==null)
            $filepath = Yii::getAlias('@tests').'/codeception/_logs'.DIRECTORY_SEPARATOR;
        
        return trim(file_get_contents($filepath.$filename));
    }
    
    public static function writeFile($filename,$content)
    {
        file_put_contents(Logger::$logPath.DIRECTORY_SEPARATOR.$filename,$content);
    }    
}
