<?php
/********************************************************************************
    Performs all actions to build file(s) of 5-cura-csv/A*.csv
    
    @license    GPL
    @history    2019-06-07 08:55:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\all\tweak2csv;
use g5\transform\cura\all\export;
use g5\transform\g55\all\edited2cura;
use g5\transform\newalch\ertel4391\fixA1;
use g5\transform\newalch\muller1083\fixCura;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Performs all actions to build file(s) of 5-cura-csv/A*.csv
        Called by : php run-g5.php cura A1 all     # A1 or A, A2 ... A6
        @param $params  array with 2 elements :
                        - the datafile (ex "A1")
                        - the command : "run"
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - all doesn't need this parameter\n";
        }
                                                                                                                                                          
        $datafile = $params[0];
        
        echo "\n=== php run-g5.php cura $datafile raw2csv small ===\n";
        $params_raw2csv = $params;
        $params_raw2csv[] = 'small';
        echo raw2csv::execute($params_raw2csv);
        
        echo "\n=== php run-g5.php cura $datafile tweak2csv small ===\n";
        echo tweak2csv::execute($params);
        
        if($datafile == 'A1'){
            echo "\n=== php run-g5.php newalch ertel4391 fixA1 update ===\n";
            echo fixA1::execute(['update']);
        }
        
        if($datafile == 'A2'){
            echo "\n=== php run-g5.php newalch muller1083 fixCura A2 names update ===\n";
            echo fixCura::execute(['A2', 'names', 'update']);
            echo "\n=== php run-g5.php newalch muller1083 fixCura A2 days update ===\n";
            echo fixCura::execute(['A2', 'days', 'update']);
        }
        
        echo "\n=== php run-g5.php cura $datafile legalTime ===\n";
        echo legalTime::execute($params);
        
        if($datafile == 'A1'){
            echo "\n=== php run-g5.php g55 570SPO edited2csv place update ===\n";
            echo edited2cura::execute([ '570SPO', 'edited2cura', 'place', 'update', ]);
            
            echo "\n=== php run-g5.php g55 570SPO edited2csv name update ===\n";
            echo edited2cura::execute([ '570SPO', 'edited2cura', 'name', 'update', ]);
        }
        
        // 
        echo "\n=== php run-g5.php cura $datafile addGeo small ===\n";
        $params_addGeo = $params;
        $params_addGeo[] = 'small';
        echo addGeo::execute($params_addGeo);
        
        echo "\n=== php run-g5.php cura $datafile export ===\n";
        echo export::execute($params);
        
        return '';
    }
    
}// end class    

