<?php
/********************************************************************************
    Importation of data/raw/cfepp/final3
    to  data/tmp/cfepp/cfepp-1120-nienhuys.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-20 18:19:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class raw2tmp implements Command {

    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Final3::rawFilename();
        $outfile = Final3::tmpFilename();
        $outfileRaw = Final3::tmpRawFilename();
        
        $report =  "--- cfepp final3 raw2tmp ---\n";
        
        $lines = Final3::loadRawFile();
        
        $N = 0;
        $res = [];
        $resRaw = []; // to keep trace of the original values
        $nLimits = count(Final3::RAW_LIMITS);
        foreach($lines as $line){
            $N++;
            $new = array_fill_keys(Final3::TMP_FIELDS, '');
            $new_raw = array_fill_keys(Final3::RAW_FIELDS, '');
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = Final3::RAW_FIELDS[$i];
                $offset = Final3::RAW_LIMITS[$i];
                $length   = Final3::RAW_LIMITS[$i+1] - Final3::RAW_LIMITS[$i];
//                $field = trim(substr($line, $offset, $length));
$field = substr($line, $offset, $length);
echo "'$field'\n";
            }
break;
        }
exit;
        
        $output = implode(G5::CSV_SEP, Final3::TMP_FIELDS) . "\n";
        foreach($res as $row){
            $output .= implode(G5::CSV_SEP, $row) . "\n";
        }
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $output);
        $report .= "Stored $n records in $outfile\n";
        
        // keep trace of the original values
        $outputRaw = implode(G5::CSV_SEP, Final3::RAW_FIELDS) . "\n";
        foreach(array_keys($res) as $k){
            $outputRaw .= implode(G5::CSV_SEP, $resRaw[$k]) . "\n";
        }
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfileRaw, $outputRaw);
        $report .= "Stored $n records in $outfileRaw\n";
        
        return $report;
    }
    
    /** Auxiliary of execute() **/
/* 
    private static function lgLat($deg, $min){
        return $deg + round(($min / 60), 5);
    }
*/
    
    /**
        Auxiliary of execute()
        Computes the timezone offset
    **/
/* 
    private static function tz($str){
        if($str == '0,5'){
            // bug for 2 records :
            // 121 Fujii Paul Takashi 1940-07-06
            // 295 Rocha Ephraim 1923-09-18
            return '-10:30';
        }
        // all other records contain integer offsets
        return '-' . str_pad($str , 2, '0', STR_PAD_LEFT) . ':00';
    }
*/
    
} // end class