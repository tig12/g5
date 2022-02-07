<?php
/******************************************************************************
    
    Builds a dump of the postgresql database, using pg_dump tool.
    Compresses this dump and stores it in g5 output directory
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-01-23 21:07:44+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\app\Config;
use g5\model\DB5;
use tiglib\patterns\Command;

class pgdump implements Command {
    
    /** 
        @param $param Empty array.
        @return Report.
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        $filename = 'ogdb-pg-dump';
        $command = 
              "PGPASSWORD='" . Config::$data['db5']['postgresql']['dbpassword'] . "'"
            . ' pg_dump'
            . ' --file ' . $filename
            . ' -h ' . Config::$data['db5']['postgresql']['dbhost']
            . ' -p ' . Config::$data['db5']['postgresql']['dbport']
            . ' -U ' . Config::$data['db5']['postgresql']['dbuser']
            . ' ' . Config::$data['db5']['postgresql']['dbname'];
        exec($command);
        $command = "zip $filename.zip $filename";
        exec($command);
        $command = "rm $filename";
        exec($command);
        $command = "mv $filename.zip " . Config::$data['dirs']['output'];
        exec($command);
        return 'Created file ' . Config::$data['dirs']['output'] . DS . "$filename.zip\n";
    }
    
} // end class
