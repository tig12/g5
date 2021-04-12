<?php
/********************************************************************************
    Import data/raw/muller/afd3-women/muller-afd3-women.txt
    to data/tmp/muller/afd3-women/muller-afd3-women.csv
    
    @license    GPL
    @history    2021-04-11, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
//use tiglib\arrays\sortByKey;

class raw2tmp implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $report =  "--- muller afd3 raw2tmp ---\n";
        
        $raw = AFD3::loadRawFile();
        $res = implode(G5::CSV_SEP, AFD3::RAW_FIELDS) . "\n";
        $nLimits = count(AFD3::RAW_LIMITS);
        $N = 0;
        foreach($raw as $line){
            $N++;
            $new = [];
            for($i=0; $i < $nLimits-1; $i++){
                $offset = AFD3::RAW_LIMITS[$i];
                $length   = AFD3::RAW_LIMITS[$i+1] - AFD3::RAW_LIMITS[$i];
                $field = trim(substr($line, $offset, $length));
                $new[AFD3::RAW_FIELDS[$i]] = $field;
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        $outfile = AFD3::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        return $report;
    }
    
}// end class    

