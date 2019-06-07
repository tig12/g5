<?php
/********************************************************************************
    Performs all actions to build data/5-tmp/cura-csv/D6.csv
    
    @license    GPL
    @history    2019-06-07 11:36:38+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\Config;
use g5\patterns\Command;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run.php cura D6 all
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{

        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - all doesn't need this parameter\n";
        }
        
        echo "\n=== execute raw2csv on D6 ===\n";
        echo raw2csv::execute($params);
        
        echo "\n=== execute addGeo on D6 ===\n";
        echo addGeo::execute($params);
        
        return '';
    }
    
}// end class    

