<?php
/********************************************************************************
    Study CFEPP test.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-04-18 11:29:15+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
//use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\ertel\sport\ErtelSport;

class look implements Command {
    
   /** Possible value for parameter 1 **/
    const POSSIBLE_PARAMS = [
        'inter' => "Computes intersections of different groups",
        'ertel' => "Lists the matching between Ertel and CFEPP",
    ];
    
    /** 
        @param $param Empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  '$k' : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}\n";
        }
        //
        $method = 'look_' . $params[0];
        return self::{$method}();
    }
    
    
    /** 
        Lists the associations with CFEPP present in Ertel file.
        Purpose: identify wrong associations in Ertel file.
        Command used to build array ErtelSport::ERTEL_CFEPP
        @return Empty string, directly echoes its result
    **/
    private static function look_ertel(): string {
        echo  "--- cfepp final3 ids group ---\n";
        $final3 = Final3::loadTmpFile_cfid();
        $ertelfile = ErtelSport::loadTmpFile();
        $fixed = array_keys(ErtelSport::ERTEL_CFEPP);
        $nInvalid = $nDiffdate = 0;
        foreach($ertelfile as $ert){
            $CFID = $ert['CFEPNR'];
            if($CFID == ''){
                continue;
            }
            $ERID = $ert['NR'];
            if(in_array($ERID, $fixed)){
                $CFID = ErtelSport::ERTEL_CFEPP[$ERID];
            }
            if(!isset($final3[$CFID])){
                $nInvalid++;
                echo "\n=====\n";
                echo "=== INVALID CFEPP ID IN ERTEL FILE ===\n";
                echo "Ertel $ERID {$ert['GNAME']} {$ert['FNAME']} {$ert['DATE']} => $CFID\n";
                continue;
            }
            $cfe =& $final3[$CFID];
            if(substr($ert['DATE'], 0, 10) == substr($cfe['DATE'], 0, 10)){
                continue;
            }
            $nDiffdate++;
            echo "\n=====\n";
            echo "Ertel $ERID {$ert['GNAME']} {$ert['FNAME']} {$ert['DATE']} => $CFID\n";
            echo "CFEPP $CFID {$cfe['GNAME']} {$cfe['FNAME']} {$cfe['DATE']}\n";
        }
        echo "============\n";
        echo "N invalid = $nInvalid\n";
        echo "N different dates = $nDiffdate\n";
        return '';
    }
    
    /** 
        Computes intersections.
        @return Report
    **/
    private static function look_inter(): string {
        $report =  "--- cfepp final3 look inter ---\n";
        
        $ids = ['CFID', 'ERID', 'GQID', 'CPID'];
        
        // base : sets containing all with CFID, ERID, GQID, CPID
        $base = [];
        foreach($ids as $id){
            $base[$id] = [];
        }
        // interesting intersections
        $inter = [
            'CF-only' => [],
            'GQ-inter-CF' => [],
            'GQ-inter-CP' => [],
            'CF-inter-CP' => [],
            'GQ-inter-CF-inter-CP' => [],
        ];
        
        $final3 = Final3::loadTmpFile();
        $fieldnames = Final3::TMP_FIELDS;
        
        foreach($final3 as $cur){
            foreach($ids as $id){
                if($cur[$id] != ''){
                   $base[$id][] = $cur;
                }
            }
            if($cur['GQID'] != '' && $cur['CFID'] != ''){
                $inter['GQ-inter-CF'][] = $cur;
            }
            if($cur['GQID'] != '' && $cur['CPID'] != ''){
                $inter['GQ-inter-CP'][] = $cur;
            }
            if($cur['CFID'] != '' && $cur['CPID'] != ''){
                $inter['CF-inter-CP'][] = $cur;
            }
            if($cur['GQID'] != '' && $cur['CFID'] != '' && $cur['CPID'] != ''){
                $inter['GQ-inter-CF-inter-CP'][] = $cur;
            }
            if($cur['GQID'] == '' && $cur['ERID'] == '' && $cur['CPID'] == ''){
                $inter['CF-only'][] = $cur;
            }
        }
        
        if(true){
            $report .= "=== base ===\n";
            foreach($base as $id => $set){
                $report .= "$id : " . count($set) . "\n";
            }
            $report .= "=== inter ===\n";
            foreach($inter as $k => $set){
                $report .= "$k : " . count($set) . "\n";
            }
        }
        return $report;
    }
        
} // end class