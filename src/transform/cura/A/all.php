<?php
/********************************************************************************
    Performs all actions to build file(s) of data/5-tmp/cura-csv/A*.csv
    
    @license    GPL
    @history    2019-06-07 08:55:40+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\Config;
use g5\patterns\Command;
use g5\transform\g55\all\edited2cura;
use g5\transform\cura\all\csv2dl;

class all implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
    Performs all actions to build file(s) of data/5-tmp/cura-csv/A*.csv
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
        
        // php run-g5.php cura A raw2csv small
        echo "\n=== Execute raw2csv on $datafile ===\n";
        $params_raw2csv = $params;
        $params_raw2csv[] = 'small';
        echo raw2csv::execute($params_raw2csv);
        
        if($datafile == 'A1'){
            // php run-g5.php cura A1 ertel2csv update
            echo "\n=== Execute ertel2csv on $datafile ===\n";
            $params_ertel2csv = $params;
            $params_ertel2csv[] = 'update';
            echo ertel2csv::execute($params_ertel2csv);
        }
        
        if($datafile == 'A2'){
            // php run-g5.php cura A2 muller2csv update
            echo "\n=== Execute muller2csv on $datafile ===\n";
            $params_muller2csv = $params;
            $params_muller2csv[] = 'update';
            echo ertel2csv::execute($params_muller2csv);
        }
        
        // php run-g5.php cura A legalTime
        echo "\n=== Execute legalTime on $datafile ===\n";
        echo legalTime::execute($params);
        
        if($datafile == 'A1'){
            // php run-g5.php g55 570SPO edited2csv place update
            echo "\n=== Correct place names on A1 from Gauquelin 1955 570SPO ===\n";
            $params_edited2cura = [
                '570SPO',
                'edited2cura',
                'place',
                'update',
            ];
            echo edited2cura::execute($params_edited2cura);
            
            // php run-g5.php g55 570SPO edited2csv name update
            echo "\n=== Correct family and given names on A1 from Gauquelin 1955 570SPO ===\n";
            $params_edited2cura[2] = 'name';
            echo edited2cura::execute($params_edited2cura);
        }
        
        // php run-g5.php cura A addGeo small
        echo "\n=== Execute addGeo on $datafile ===\n";
        $params_addGeo = $params;
        $params_addGeo[] = 'small';
        echo addGeo::execute($params_addGeo);
        
        // php run-g5.php cura A csv2dl
        echo "\n=== Execute csv2dl on $datafile ===\n";
        echo csv2dl::execute($params);
        
        return '';
    }
    
}// end class    

