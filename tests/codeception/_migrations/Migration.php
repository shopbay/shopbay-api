<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\_migrations;

use Yii;
Yii::import('console.extensions.txsql.TXFile');
Yii::import('console.extensions.txsql.TXFsObject');
Yii::import('console.extensions.txsql.TXObject');
use \TXFile;
use \TXFsObject;
use \TXObject;
/**
 * Description of Migration
 * Added support to run sql file
 * 
 * @author kwlok
 */
class Migration extends \yii\db\Migration
{
    const SQL_COMMAND_DELIMETER = ';';
    const SQL_COMMENT_SYMBOL    = '--';
    
    protected function loadSchema($moduleAlias,$sqlFile='schema.sql',$dataFile=null)
    {
        $sql = Yii::getPathOfAlias($moduleAlias.'.data').DIRECTORY_SEPARATOR.$sqlFile;
        $this->executeFile($sql);        
        if (isset($dataFile)){
            $sql = Yii::getPathOfAlias($moduleAlias.'.data').DIRECTORY_SEPARATOR.$dataFile;
            $this->executeFile($sql);        
        }
        
    }       
    
    protected function _infoLine($filePath, $next = null) 
    {
        echo "\r    > execute file $filePath ..." . $next;
    }
  
    public function executeFile($filePath) 
    {
        $this->_infoLine($filePath);
        $time=microtime(true);
        $file = new TXFile(array(
          'path' => $filePath,
        ));

        if (!$file->exists)
            throw new Exception("'$filePath' is not a file");

        try {
            
            if ($file->open(TXFile::READ) === false)
                throw new Exception("Can't open '$filePath'");

            $total = floor($file->size / 1024);
            $command='';
            while (!$file->endOfFile()) {
                $line = $file->readLine();
                $line = trim($line);
                if (empty($line))
                    continue;
                $current = floor($file->tell() / 1024);
                $this->_infoLine($filePath, " $current of $total KB");
                $command .= $line;
                if (strpos($line,self::SQL_COMMAND_DELIMETER)){
                    if (substr($command, 0, strlen(self::SQL_COMMENT_SYMBOL))==self::SQL_COMMENT_SYMBOL){
                        //$this->_infoLine($filePath, " skip comment:  ".$command."\n");
                    }
                    else {
                        $this->_infoLine($filePath, " executing command:  ".$command."\n");
                        $this->execute($command);
                    }
                    $command = '';
                }
            }
            $file->close();
          
        } catch (Exception $e) {
            $file->close();
            var_dump($line);
            throw $e;
        }
        $this->_infoLine($filePath, " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n");
    } 

}
