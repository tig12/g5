<?php
/********************************************************************************
    Adds columns GQID ERID CPID in data/tmp/cfepp/cfepp-1120-nienhuys.csv
    Uses Ertel 4391 file.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-27 23:30:58+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\model\Person;
use g5\commands\cfepp\final3\Final3;
use g5\commands\ertel\Ertel;
use g5\commands\ertel\sport\ErtelSport;
use tiglib\patterns\Command;

class ids implements Command {
    
   /** Possible value for parameter 1 **/
    const POSSIBLE_PARAMS = [
        'add' => "Add values ERID GQID CPID",
        'group' => "Compute unique and intersections",
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
        $action = $params[0];
        return self::{$action}();
    }
    
    /** 
    
    **/
    public static function add(): string {
        $report =  "--- cfepp final3 ids add ---\n";
        
        // Array to complete: add values in columns ERID GQID CPID
        $final3 = Final3::loadTmpFile_cfid();
        // data/tmp/ertel/ertel-4384-sport.csv : 4384 rows
        $ertelfile = ErtelSport::loadTmpFile();
        $NCFEPP = $NnotCFEPP = 0;
        foreach($ertelfile as $cur){
            $CFID = $cur['CFEPNR'];
            if($CFID == ''){
                $NnotCFEPP++;
                continue;
            }
            if(!isset($final3[$CFID])){
                // Happens for 4 records - errors in Ertel file
                // echo "CFID = '$CFID' - NR = {$cur['NR']} - {$cur['FNAME']} {$cur['GNAME']}\n";
                // CFID = '1921' - NR = 512 - Bollelli Henri
                // CFID = '0' - NR = 1990 - Gruppi Raymond
                // CFID = '0' - NR = 2001 - Guerin Henri
                // CFID = '0' - NR = 3181 - Pecqueux Michel
                // Fix one case after manual check (compare Ertel - Final3)
                if($cur['NR'] == 512){
                    $CFID = 1021;
                }
                else{
                    // 3 other cases not present in final3 (error in Ertel file)
                    $NnotCFEPP++;
                    continue;
                }
            }
            $NCFEPP++;
            $final3[$CFID]['ERID'] = Ertel::ertelId('S', $cur['NR']);
            $final3[$CFID]['GQID'] = ErtelSport::GQIDfrom3a_sports($cur);
            $final3[$CFID]['CPID'] = $cur['PARA_NR'];
        }
        
        // store modified tmp file 
        $outfile = Final3::tmpFilename();
        $out = implode(G5::CSV_SEP, Final3::TMP_FIELDS) . "\n";
        foreach($final3 as $cur){
            $out .= implode(G5::CSV_SEP, $cur) . "\n";
        }
        file_put_contents($outfile, $out);
        
        $report .=  "Modified $NCFEPP records, added ERID, GQID, CPID in $outfile\n";
        return $report;
    }
    
    /** 
        Creates
        @return Report
    **/
    public static function group(): string {
        $report =  "--- cfepp final3 ids group ---\n";
        
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
