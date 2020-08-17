<?php
/********************************************************************************
    Performs all actions to build 5-csicop/408-csicop-irving.csv
    
    @license    GPL
    @history    2019-12-24 11:07:04+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\patterns\Command;

class all implements Command {
    
    // *****************************************
    /** 
        Performs all actions to build file 5-csicop/408-csicop-irving.csv
        @param $param Empty array
        @return       Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        echo "\n=== php run-g5.php csicop irving raw2csv ===\n";
        echo raw2csv::execute();
        
        echo "\n=== php run-g5.php csicop irving addD10 ===\n";
        echo addD10::execute();
        
        //echo "\n=== php run-g5.php csicop irving export dl ===\n";
        //echo export::execute(['dl']);

        return '';
    }
    
}// end class
