<?php
/********************************************************************************
    Performs all actions to build all files of data/5-tmp/cura-csv
    
    @license    GPL
    @history    2019-06-07 12:28:14+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\all;

use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\A\all as allA;
use g5\transform\cura\D6\all as allD6;
use g5\transform\cura\D10\all as allD10;
use g5\transform\cura\E1_E3\all as allE1_E3;


class run implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura all run
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{

        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - all doesn't need this parameter\n";
        }
        
// @todo use instead CuraRouter::computeDatafiles('A')
        $datafilesA = ['A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
        foreach($datafilesA as $datafile){
            $params[0] = $datafile;                                                 
            allA::execute($params);
        }
        
// @todo why not arrays ? => see if commands accept arrays, in particular E1 E3
        $params[0] = 'D6';
        allD6::execute($params);
        
        $params[0] = 'D10';
        allD10::execute($params);
        
        $params[0] = 'E1';
        allE1_E3::execute($params);
        
        $params[0] = 'E3';
        allE1_E3::execute($params);
        
        return "Done\n";
    }
    
}// end class    

