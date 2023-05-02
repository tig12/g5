<?php
/********************************************************************************
    Handles particular cases of g55 import

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
        $param = $params[2];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $report = '';
        
        $method = 'exec_' . $param;
        $report .= self::$method();
        
        return $report;
    }
    
    
    /**
        Modifies files data/tmp/gauq/g55/09-349-scientists.csv and 09-349-scientists-raw.csv :
        adds lines marked with * in files 01-576-physicians.csv and 01-576-physicians-raw.csv
        (persons that are both members of academy of medecine and members of academy of sciences)
        @pre The following commands must have been executed before :
            php run-g5.php gauq g55 raw2tmp 01-576-physicians
            php run-g5.php gauq g55 gqid update 01-576-physicians
            php run-g5.php gauq g55 raw2tmp 09-349-scientists
            php run-g5.php gauq g55 gqid update 09-349-scientists
    **/
    private static function exec_complete09(): string{
        $report = "--- gauq g55 special complete09 09-349-scientists\n";
        
        $file09 = G55::tmpFilename('09-349-scientists');
        $file09_raw = G55::tmpRawFilename('09-349-scientists');

        $lines01 = G55::loadTmpFile('01-576-physicians');
        $lines01_raw = G55::loadTmpRawFile('01-576-physicians');
        
        $res = file_get_contents($file09);
        $res_raw = file_get_contents($file09_raw);
        
        $N = count($lines01);
        $N_added = 0;
        for($i=0; $i < $N; $i++){
            $line =& $lines01[$i];
            if($line['OTHER'] == ''){
                continue;
            }
            $N_added++;
            $line_raw =& $lines01_raw[$i];
            $res .= implode(G5::CSV_SEP, $line) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $line_raw) . "\n";
        }
        
        file_put_contents($file09, $res);
        file_put_contents($file09_raw, $res_raw);
        
        $report .= "Added $N_added persons to data/tmp/gauq/g55/09-349-scientists.csv and 09-349-scientists-raw.csv\n";
        return $report;
    }
    
    
} // end class    
