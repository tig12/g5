<?php
/********************************************************************************
    Performs all actions to build 
    
    @license    GPL
    @history    2019-10-18 12:01:27+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

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
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        echo "\n=== php run-g5.php newalch muller402 raw2full ===\n";
        echo raw2full::execute();
        
        echo "\n=== php run-g5.php newalch muller402 export dl ===\n";
//        echo export::execute(['dl']);

        
        return '';
    }
    
}// end class
