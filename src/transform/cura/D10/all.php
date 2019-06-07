<?php
/********************************************************************************
    Performs all actions to build data/5-tmp/cura-csv/D10.csv
    
    @license    GPL
    @history    2019-06-07 12:01:21+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D10;

use g5\Config;
use g5\patterns\Command;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura D10 all
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{

        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - all doesn't need this parameter\n";
        }
        
        echo "\n=== execute raw2csv on D10 ===\n";
        echo raw2csv::execute($params);
        
        return '';
    }
    
}// end class    

