<?php
/********************************************************************************
    Performs all actions to build data/5-tmp/cura-csv/E1.csv and E3.csv
    
    @license    GPL
    @history    2019-06-07 12:17:19+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\E1_E3;

use g5\Config;
use g5\patterns\Command;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by :
            php run-g5.php cura E1 all
            php run-g5.php cura E3 all
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{

        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - all doesn't need this parameter\n";
        }
        
        $params_raw2csv = $params;
        $params_raw2csv[] = 'small';
        echo "\n=== execute raw2csv on {$params[0]} ===\n";
        echo raw2csv::execute($params_raw2csv);
        
        return '';
    }
    
}// end class    

