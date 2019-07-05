<?php
/********************************************************************************
    Performs all actions to build data/5-tmp/cura-csv/D6.csv
    
    @license    GPL
    @history    2019-06-07 11:36:38+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\all\csv2dl;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura D6 all
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
        
        echo "\n=== Execute csv2dl on D6 ===\n";
        echo csv2dl::execute($params);
        
        return '';
    }
    
}// end class    

