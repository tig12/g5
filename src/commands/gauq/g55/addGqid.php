<?php
/********************************************************************************
    Computes field GQID for files in data/tmp/gauq/g55/
    Useful for groups published in LERRCPbooklets (not painters and priests).

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-06-05 16:38:03+02:00, Thierry Graff : creation (but not implementation)
********************************************************************************/
namespace g5\commands\gauq\g55;

use tiglib\patterns\Command;
use g5\DB5;

class addGqid implements Command {
    
    /**
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "tmp2gqid" (useless here, used by GauqCommand).
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
    **/
    public static function execute($params=[]): string {
        
        $cmdSignature = 'gauq g55 addGqid';
        $report = "--- $cmdSignature ---\n";
        
        $possibleParams = G55::getPossibleGroupKeys();
        $msg = "Usage : php run-g5.php $cmdSignature <group>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        
        if(count($params) != 3){
            return "INVALID CALL: - this command needs exactly one parameter.\n$msg";
        }
        $groupKey = $params[2];
        if(!in_array($groupKey, $possibleParams)){
            return "INVALID PARAMETER: $groupKey\n$msg";
        }
        
        $tmpfile = G55::tmpFilename($groupKey);
        if(!is_file($tmpfile)){
            return "UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n";
        }
        
        return "=== FUNCTION NOT IMPLEMENTED ===\n";
    }
    
} // end class
