<?php
/********************************************************************************
    Performs all actions to build 5-newalch-csv/4091SPO.csv
    
    @license    GPL
    @history    2019-10-24 00:01:52+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\Ertel4391;

use g5\G5;
use g5\patterns\Command;

class all implements Command {
    
    // *****************************************
    /** 
        Performs all actions to build file 5-newalch-csv/4091SPO.csv
        @param $param Empty array
        @return       Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        echo "\n=== php run-g5.php newalch ertel4391 raw2csv ===\n";
        echo raw2csv::execute();
        
        echo "\n=== php run-g5.php newalch ertel4391 tweak2csv ===\n";
        echo tweak2csv::execute();
        
        echo "\n=== php run-g5.php newalch ertel4391 export dl ===\n";
        echo export::execute(['dl']);

        return '';
    }
    
}// end class
