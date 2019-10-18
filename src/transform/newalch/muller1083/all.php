<?php
/********************************************************************************
    Performs all actions to build 
    
    @license    GPL
    @history    2019-10-18 12:01:27+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\G5;
use g5\patterns\Command;

class all implements Command {
    
    // *****************************************
    /** 
        Performs all actions to build file 5-newalch-csv/1083MED.csv
        @param $param Empty array
        @return       Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "USELESS PARAMETER in g5\\transform\\newalch\\muller1083\\all : {$params[0]}\n";
        }
        
        // php run-g5.php newalch muller1083 raw2csv
        echo "\n=== Execute raw2csv ===\n";
        echo raw2csv::execute();
        
        // php run-g5.php newalch muller1083 fixGnr update
        echo "\n=== Execute fixGnr ===\n";
        echo fixGnr::execute(['update']);
        
        return '';
    }
    
}// end class
