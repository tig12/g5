<?php
/********************************************************************************
    
    Updates opengauquelin database from files located in data/db/enrich/death
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-02-01 16:45:07+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use g5\model\DB5;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;

class csv2db implements Command {
    
    private static \PDO $db5;
    
    /** Directory containing the csv files to process **/
    const string DATA_DIR = 'data/db/enrich/death-fr';
    
    /** 
        @param $params  Array containing zero or one element.
                        One element: path to a csv file to process, relative to data/db/enrich/death-fr/
                        Zero element: match all files of data/db/enrich/death-fr/
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // check params
        //
        $msg = "this command needs zero or one parameter, indicating the file to match\n"
                . "Ex: php run-g5.php enrich deathfr csv2db     => process all the csv files\n"
                . "    php run-g5.php enrich deathfr csv2db death-fr-ok.csv\n";
        if(count($params) > 1){
            return "INVALID CALL: $msg";
        }
        if(count($params) == 1){
            $files = [$params[0]];
        }
        else{
            $tmp = glob(self::DATA_DIR . DS . '*');
            $files = [];
            foreach($tmp as $file){
                if(strpos($file, 'README') === false){
                    $files[] = $file;
                }
            }
        }
        
        foreach($files as $file){
            $file = 'compress.zlib://' . Config::$data['dirs']['ROOT'] . DS . $file;
            echo "======= Processing $file =======\n";
            $entries = csvAssociative::compute($file);
            foreach($lines as $line){
print_r($line); exit;
            }
        }// end loop $files
        
        
    }
    
}// end class
/* 
When I execute the following code in php 8:5 on debian 13:

$filename = 'compress.zip:///path/to/myfile.zip';
$handle = fopen($filename, "r");

I have this error:
PHP Warning:  fopen(): Unable to find the wrapper "compress.zip"

I have already installed the zip extension with
sudo apt install php8.5-zip

What can I do?
*/