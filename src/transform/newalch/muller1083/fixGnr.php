<?php
/********************************************************************************
    Builds Muller1083::GNR_FIX
    
    @license    GPL
    @history    2019-10-15 01:24:44+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\G5;
use g5\patterns\Command;
use g5\transform\cura\Cura;

class fixGnr implements Command {
    
    const POSSIBLE_PARAMS = [
        'update' => "Updates file MUL1083MED and echoes minimal report",
        'report' => "Echoes a full list of modifications without modifying anything",
        'check' => "Execute after fix2gnr. Compares the dates with A2 E1 to check if everything was fixed",
    ];
    
    // *****************************************
    /** 
        Routes to the different actions, based on $param
        @param $param Array containing one element (a string)
                      Must be one of self::POSSIBLE_PARAMS
    **/
    public static function execute($params=[]): string{
        
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  '$k' : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING in g5\\transform\\newalch\\muller1083\\fixGnr - This function needs one parameter\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER in g5\\transform\\newalch\\muller1083\\fixGnr : {$params[1]}\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER in g5\\transform\\newalch\\muller1083\\fixGnr\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        if($param == 'check'){
            return self::check();
        }
        
        $report = '';
        $corrections = [];
        $nomatch = false;
        
        // assoc arrays, keys = NUM
        $a2s = Cura::loadTmpCsv_num('A2');
        $e1s = Cura::loadTmpCsv_num('E1');
        
        $MullerCsv = Muller1083::loadTmpFile();
        
        // Build an array containing duplicate values of GNR in 1083MED.csv
        $mullers = [];
        
        $tmp = [];
        foreach($MullerCsv as $row){
            $gnr = $row['GNR'];
            if($gnr == ''){
                continue;
            }
            if(!isset($tmp[$gnr])){
                $tmp[$gnr] = [];
            }
            $tmp[$gnr][] = [
                'NR' => $row['NR'],
                'FNAME' => $row['FNAME'],
                'GNAME' => $row['GNAME'],
                'DATE' => $row['DATE'],
            ];
        }
        // keep only duplicates
        foreach($tmp as $gnr => $val){
            if(count($val) == 1){
                continue; // no duplicates, ok
            }
            $mullers[$gnr] = $val;
        }
        
        foreach($mullers as $GNR => $muller){
            $curaPrefix = substr($GNR, 0, 3); // SA2 or ND1
            if($curaPrefix == 'SA2'){
                $curaFile =& $a2s;
                $curaFilename = 'A2';
            }
            else{
                $curaFile =& $e1s;
                $curaFilename = 'E1';
            }
            
            $NUM_muller = substr($GNR, 3);
            $targetNUMs = self::targetNums($NUM_muller);
            
            // Check if ambiguity on birth day in muller records
            // As correction is built using birth day, an ambiguity would possibly introduce wrong matching 
            // Result : no ambiguity ; in practice this check is useless.
            $dates_muller = [];
            foreach($muller as $rec){
                $date = substr($rec['DATE'], 0, 10); // compare only days
                if(in_array($date, $dates_muller)){
                    $report .= "DUPLICATE DATE IN MULLER : GNR = $GNR - DATE = $date\n";
                    $report .= "Case not fixed\n";
                    continue 2; // next muller record
                }
                $dates_muller[] = $date;
            }
            
            foreach($muller as $rec_muller){
                $NR = $rec_muller['NR'];
                $date_muller = substr($rec_muller['DATE'], 0, 10);
                $candidates = [];
                $curaTests = []; // only useful to log NO MATCHING case
                foreach($targetNUMs as $targetNUM){
                    $date_cura = substr($curaFile[$targetNUM]['DATE'], 0, 10);
                    $curaTests[$targetNUM] = $date_cura;
                    if($date_muller == $date_cura){
                        $candidates[] = $targetNUM;
                    }
                }
                if(count($candidates) == 0){
                    // This happens if corrections tweak2csv are not applied to A2 and E1 before executing fixGnr
                    $nomatch = true;
                    $report .= "NO MATCHING for Muller NR = $NR - GNR = $GNR\n";
                    $report .= "    Muller : $date_muller | {$rec_muller['FNAME']} | {$rec_muller['GNAME']}\n";
                    $report .= "    Possible Cura $curaFilename :\n";
                    foreach($curaTests as $k => $v){
                        $report .= "        $k : $v | {$curaFile[$k]['FNAME']} | {$curaFile[$k]['GNAME']}\n";
                    }
                    continue;
                }
                if(count($candidates) > 1){
                    // If several Gauquelin candidates have the same date
                    // Does not occur, in practice this test is useless
                    $report .= "AMBIGUITY for Muller NR = $NR - GNR = $GNR\n";
                    $report .= "    Possible matches in cura $curaFilename : " . implode($candidates, ', ') . "\n";
                    continue;
                }
                // found a unique match
                $corrections[$NR] = $curaPrefix . $candidates[0];
            }
        }
        
        $nCorr = count($corrections);
        if($param == 'update' && !$nomatch){
            $res = implode(G5::CSV_SEP, array_keys($MullerCsv[0])) . "\n";
            foreach($MullerCsv as $row){
                $new = $row;
                if(isset($corrections[$row['NR']])){
                    $new['GNR'] = $corrections[$row['NR']];
                }
                $res .= implode(G5::CSV_SEP, $new) . "\n";
            }
            $destFile = Muller1083::tmp_csv_filename();
            file_put_contents($destFile, $res);// HERE modify 1083MED.csv
            $report .= "$destFile was updated\n";
            $report .= "$nCorr GNR were corrected\n";
        }
        else if($param == 'report'){
            $report .= "This function will modify the following records of 1083MED.csv :\n";
            $report .= "  NR\t | Corrected GNR\n";
            foreach($corrections as $NR => $correction){
                $report .= "  $NR\t | $correction\n";
            }
            $report .= "Total : $nCorr GNR need correction\n";
        }
        
        if($nomatch){
            $report .= "===   Some corrections do not match   ===\n";
            $report .= "===        I Refuse to fix GNR        ===\n";
            $report .= "  First apply tweak2csv to A2 and E1 : \n";
            $report .= "  php run-g5.php cura A2 tweak2csv\n";
            $report .= "  php run-g5.php cura E1 tweak2csv\n";
            $report .= "  Then you can re-execute fixGnr\n";
        }
        
        return $report;
    }
    
    // ******************************************************
    /**
        Auxiliary of execute()
        Given a NUM in muller file, computes the possible matching NUM in cura files.
        ex : $NUM = 103 => returns [103, 1030, 1031 ... 1039]
        @param $NUM Muller NUM, computed from GNR
    **/
    private static function targetNums($NUM){
        $res = [$NUM];
        $x10 = $NUM * 10;
        for($i=0; $i < 10; $i++){
            $res[] = $x10 + $i;
        }
        return $res;
    }
    
    // ******************************************************
    /**
        Implements php run-g5.php newalch muller1083 fixGnr check
    **/
    private static function check(){
        $ndiff = $ncommon = 0;
        $a2s = Cura::loadTmpCsv_num('A2');
        $e1s = Cura::loadTmpCsv_num('E1');
        $MullerCsv = Muller1083::loadTmpFile();
        foreach($MullerCsv as $mulrow){
            $GNR = $mulrow['GNR'];
            $curaPrefix = substr($GNR, 0, 3); // SA2 or ND1
            if($curaPrefix == ''){
                continue;
            }
            $ncommon++;
            if($curaPrefix == 'SA2'){
                $curaFile =& $a2s;
                $curaFilename = 'A2';
            }
            else{
                $curaFile =& $e1s;
                $curaFilename = 'E1';
            }
            $NUM = substr($GNR, 3);
            $date_m = substr(trim($mulrow['DATE']), 0, 10);
            $curarow = $curaFile[$NUM];
            $date_g = substr(trim($curarow['DATE']), 0, 10);
            if($date_g != $date_m){
                $ndiff++;
                echo "Difference\n";
                echo "  Muller $date_m NR = {$mulrow['NR']} | {$mulrow['FNAME']} {$mulrow['GNAME']} \n";
                echo "  $curaFilename\t $date_g NUM = $NUM | {$curarow['FNAME']} {$curarow['GNAME']}\n";
            }
        }
        echo "nb common (A2 + E1) : $ncommon\n";
        echo "nb differences (A2 + E1) : $ndiff\n";
        return '';
    }
    
    
}// end class
