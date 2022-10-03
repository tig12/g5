<?php
/********************************************************************************
    Handles particular cases of g import

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-10-03 23:14:42+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\G5;
use tiglib\patterns\Command;

class special implements Command {
    
    const POSSIBLE_PARAMS = [
        'complete09' => "Complete 09-349-scientists.csv with rows of 01-576-physicians",
    ];
    
    // ******************************************************
    /** 
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "special" (useless here, used by GauqCommand).
                        - a string identifying the action to perform (a key of self::POSSIBLE_PARAMS).
        @return report
    **/
    public static function execute($params=[]): string{
        
        $cmdSignature = 'gauq g55 special';
        
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "    $k : $v\n";
        }
        if(count($params) != 3){
            return "INVALID CALL: - this command needs exactly one parameter.\n$msg";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $method = 'exec_' . $param;
        
        return $report;
    }
        
} // end class    
