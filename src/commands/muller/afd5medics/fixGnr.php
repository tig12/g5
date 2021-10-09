<?php
/********************************************************************************
    Fixes the problem of truncated GNR in data/tmp/newalch/1083MED.csv
    
    @license    GPL
    @history    2019-10-15 01:24:44+02:00, Thierry Graff : Creation
    @history    2019-10-16 23:31:44+02:00, Thierry Graff : New version, using date differences instead of GNR doublons
********************************************************************************/
namespace g5\commands\muller\afd5medics;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\Cura;

class fixGnr implements Command {
    
    const POSSIBLE_PARAMS = [
        'update' => "Updates column GNR of file data/tmp/newalch/1083MED.csv",
        'report' => "Echoes a full list of GNR corrections without modifying data/tmp/newalch/1083MED.csv",
    ];
    
    // *****************************************
    /** 
        Fixes the problem of truncated GNR
        @param $param Array containing one element (a string)
                      Must be one of self::POSSIBLE_PARAMS
    **/
    public static function execute($params=[]): string{
        
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  '$k' : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $report = "--- muller1083 fixGnr ---\n";
        
        // assoc arrays
        $a2s = @Cura::loadTmpFile_num('A2'); // keys = NUM
        if(empty($a2s)){
            return "File data/tmp/cura/A2.csv doesn't exist\n"
                . "Build this file before executing fixGnr \n";
        }
        $e1s = @Cura::loadTmpFile_num('E1'); // keys = NUM
        if(empty($e1s)){
            return "File data/tmp/cura/E1.csv doesn't exist\n"
                . "Build this file before executing fixGnr \n";
        }
        $MullerCsv = Muller1083::loadTmpFile_nr(); // keys = NR
        $maxNUM_A2 = 3647;
        $maxNUM_E1 = 2154;
        
        // Corrections that will be applied to GNR
        // key = NR ; value = corrected GNR
        // Among these 22 corrections, only 3 will be applied
        // Other corrections permit to fix a date error, but are not real corrections.
        $corrections = [
            '157' => 'SA2117',
            '193' => 'SA2144',
            '235' => 'SA2175',
            '251' => 'SA2186',
            '252' => 'SA2187',
            '320' => 'SA2237',
            '329' => 'SA2243',
            '332' => 'SA2245',
            '342' => 'SA2255',
            '366' => 'SA2271',
            '374' => 'SA2278',
            '387' => 'SA2287',
            '429' => 'SA2311',
            '482' => 'SA2345',
            '484' => 'SA21051', // Differs from SA2105
            '485' => 'SA2347',
            '490' => 'SA2349',
            '492' => 'SA2351',
            '499' => 'SA2356',
            '500' => 'SA2357',
            '743' => 'ND11520', // differs from ND1152
            '914' => 'ND11806', // differs from ND1180
        ];
        
        $ambiguous = false;
        
        foreach($MullerCsv as $NR => $mulrow){
            if(isset($corrections[$NR])){
                continue;
            }
            $GNR = $mulrow['GNR'];
            if($GNR == ''){
                continue;
            }
            $curaPrefix = substr($GNR, 0, 3); // SA2 or ND1
            if($curaPrefix == 'SA2'){
                $curaFile =& $a2s;
                $curaFilename = 'A2';
                $curaDateField = 'DATE-UT';
            }
            else{
                $curaFile =& $e1s;
                $curaFilename = 'E1'; 
                $curaDateField = 'DATE';
            }
            
            $NUM_muller = substr($GNR, 3);
            
            if($NUM_muller < 100){
                continue; // not truncated => no correction needed
            }
            
            $max = ($curaFilename == 'A2' ? $maxNUM_A2 : $maxNUM_E1);
            $targetNUMs = self::targetNums($NUM_muller, $max);
            
            if(count($targetNUMs) == 1 && $targetNUMs[0] == $NUM_muller){
                continue;
            }
            
            $date_muller = substr($mulrow['DATE'], 0, 10);
            $candidates = [];
            $curaTests = []; // only useful to log NO MATCHING case
            foreach($targetNUMs as $targetNUM){
                if(!isset($curaFile[$targetNUM])){
                    continue; // happens for ex because A2 2652 doesn't exist
                }
                $date_cura = substr($curaFile[$targetNUM][$curaDateField], 0, 10);
                if($date_muller == $date_cura){
                    $candidates[] = $targetNUM;
                }
                $curaTests[$targetNUM] = $date_cura;
            }
            if(count($candidates) == 0){
                $ambiguous = true;
                // This test happens if the case is not included in $corrections initialization
                // This display was used to build $corrections
                $report .= "NO MATCHING for Muller NR = $NR - GNR = $GNR\n";
                $report .= "Probable fix, to add in the initialization of \$corrections : \n";
                $report .= "            '$NR' => '$GNR',\n";
                $report .= "Details :\n";
                $report .= "    Muller : $date_muller | {$mulrow['FNAME']} | {$mulrow['GNAME']}\n";
                $report .= "    Possible Cura $curaFilename :\n";
                foreach($curaTests as $k => $v){
                    $report .= "        $k : $v | {$curaFile[$k]['FNAME']} | {$curaFile[$k]['GNAME']}\n";
                }
                continue;
            }
            else if(count($candidates) > 1){
                // If several Gauquelin candidates have the same date
                // Does not occur, so in practice this test is useless
                $report .= "AMBIGUITY for Muller NR = $NR - GNR = $GNR\n";
                $report .= "    Possible matches in cura $curaFilename : " . implode($candidates, ', ') . "\n";
                continue;
            }
            // count($candidates) = 1
            if($candidates[0] != $NUM_muller){
                $corrections[$NR] = $curaPrefix . $candidates[0];
            }
        }
        
        // Filter corrections to eliminate useless corrections
        // added in the initializations of $corrections to handle date problems
        foreach($MullerCsv as $NR => $mulrow){
            $GNR = $mulrow['GNR'];
            if(!isset($corrections[$NR])){
                continue;
            }
            if($corrections[$NR] == $GNR){
                unset($corrections[$NR]);
            }
        }
        
        $nCorr = count($corrections);
        
        if($param == 'report'){
            $report .= "This function will modify the following records of 1083MED.csv :\n";
            foreach($corrections as $NR => $newGNR){
                if(substr($newGNR, 0, 3) == 'SA2'){
                    $curaFile =& $a2s;
                }
                else{
                    $curaFile =& $e1s;
                }
                $NUM = substr($newGNR, 3);
                $date = substr($MullerCsv[$NR]['DATE'], 0, 10);
                $report .= "Muller : NR $NR - GNR {$MullerCsv[$NR]['GNR']} -\t $date | {$MullerCsv[$NR]['FNAME']} | {$MullerCsv[$NR]['GNAME']}\n";
                
                $date = isset($curaFile[$NUM]['DATE'])
                    ? substr($curaFile[$NUM]['DATE'], 0, 10) // in E1
                    : substr($curaFile[$NUM]['DATE-UT'], 0, 10); // in A2
                $report .= "Corrected : $newGNR - in Cura :\t $date | {$curaFile[$NUM]['FNAME']} | {$curaFile[$NUM]['GNAME']}\n";
                $report .= "\n";
            }
            $report .= "Total : $nCorr GNR will be corrected\n";
            return $report;
        }
        
        if($ambiguous){
            $report .= "GNR corrections can't be applied.\n";
            $report .= "First fix in the code the NO MATCHING cases in the initialisation of \$corrections\n";
            $report .= "Nothing was modified.\n";
            return $report;
        }
        
        // update 1083MED.csv
        $res = implode(G5::CSV_SEP, array_keys($MullerCsv[1])) . "\n";
        foreach($MullerCsv as $NR => $row){
            $new = $row;
            if(isset($corrections[$NR])){
                $new['GNR'] = $corrections[$NR];
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        $destFile = Muller1083::tmpFilename();
        file_put_contents($destFile, $res);// HERE update 1083MED.csv
        $report .= "Corrected $nCorr GNR in $destFile\n";
        
        return $report;
    }
    
    // ******************************************************
    /**
        Auxiliary of execute()
        Given a NUM in muller file, computes the possible matching NUM in cura files.
        ex : $NUM = 103 => returns [103, 1030, 1031 ... 1039]
        @param $NUM Muller NUM, computed from GNR
    **/
    private static function targetNums($NUM, $max){
        $res = [$NUM];
        $x10 = $NUM * 10;
        for($i=0; $i < 10; $i++){
            $n = $x10 + $i;
            if($n > $max){
                break;
            }
            $res[] = $x10 + $i;
        }
        return $res;
    }
    
}// end class
