<?php
/********************************************************************************
    Uses data/tmp/ertel/ertel-4384-sportsmen.csv to compute missing names in data/tmp/gauq/lerrcp/A1.csv
    
    Operates only for file A1
    
    @pre        data/tmp/gauq/lerrcp/A1.csv must exist.
                (so src/commands/gauq/A/raw2tmp.php must have been executed before).
    @pre        data/tmp/ertel/ertel-4384-sportsmen.csv must exist
                (so src/commands/ertel/ertel4391/raw2tmp.php must have been executed before).
    @license    GPL
    @history    2019-05-18 07:06:41+02:00, Thierry Graff : creation
                2021-11-11 06:43:00+01:00, Thierry Graff : adaptation to new structure
********************************************************************************/
namespace g5\commands\ertel\ertel4391;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;
use g5\commands\gauq\LERRCP;

class fixA1 implements Command {
    
    /** 
        Called by : php run-g5.php ertel ertel4391 fixA1 [update|report]
        @param $params array that must contain 1 string : 'echo' or 'update'
    **/
    public static function execute($params=[]): string{
        
        $err_msg = "WRONG USAGE - fixA1 needs one parameter. Can be :\n"
                 . "  'report' : echoes the list of names that will be modified by 'update'\n"
                 . "  'update' : updates file A1\n";
        
        if(count($params) != 1){
            return $err_msg;
        }
        
        $action = $params[0];
        if(!in_array($action, ['report', 'update'])){
            return $err_msg;
        }
        
        $report = "--- Ertel4391 fixA1 ---\n";
        
        $gauqRows = csvAssociative::compute(LERRCP::tmpFilename('A1'));
        $erteRows = csvAssociative::compute(Ertel4391::tmpFilename());
        
        // build $gauqMissing
        // assoc arrays with NUM as key
        $gauqMissing = [];
        foreach($gauqRows as $row){
            if(strpos($row['FNAME'], 'Gauquelin-A1-') === 0){
                $gauqMissing[$row['NUM']] = $row;
            }
        }
        
        // load lines with A1 reference in ertel file
        // assoc array with G_NR (= NUM in gauq) as key
        $ertelA1 = [];
        foreach($erteRows as $row){
            if(substr($row['QUEL'], 0, 5) == 'G:A01'){
                $gnr = $row['G_NR'];
                $ertelA1[$row['G_NR']] = $row;
            }
        }
        
        //
        // Action
        //
        
        $nCorrected = 0;
        
        // Update mode - modify A1.csv
        if($action == 'update'){
            $res = implode(G5::CSV_SEP, array_keys($gauqRows[0])) . "\n";
            $missingNums = array_keys($gauqMissing);
            foreach($gauqRows as $row){
                $NUM = $row['NUM'];
                if(in_array($NUM, $missingNums)){
                    $tmp = $gauqMissing[$NUM];
                    $tmp['FNAME'] = $ertelA1[$NUM]['FNAME'];
                    $tmp['GNAME'] = $ertelA1[$NUM]['GNAME'];
                    $res .= implode(G5::CSV_SEP, $tmp) . "\n";
                    $nCorrected++;
                }
                else{
                    $res .= implode(G5::CSV_SEP, $row) . "\n";
                }
            }
            $outfile = LERRCP::tmpFilename('A1');
            $report .= "Corrected $nCorrected names in data/tmp/gauq/lerrcp/A1.csv\n";
            file_put_contents($outfile, $res);
        }
        
        // Report mode - use for visual check that birth dates match
        else if($action == 'report'){
            foreach($gauqMissing as $num => $gauqRow){
                if(isset($ertelA1[$num])){
                    $ertelRow =& $ertelA1[$num];
                    $report .= "\n" . $num . ' ' . $ertelRow['FNAME'] . ' ' . $ertelRow['GNAME'] . "\n";
                    $report .= $gauqRow['DATE-UT'] . "\n" . $ertelRow['DATE'] . "\n";
                    $nCorrected++;
                }
            }
            $report .= "-----------\n";
            $report .= 'Nb missing names in Gauquelin A1 : ' . count($gauqMissing) . "\n";
            $report .= 'Ertel 4391 contains : ' . count($ertelA1) . " lines from A1\n"; 
            $report .= "Nb corrections : $nCorrected\n";
        }
        
        return $report;
    }
    
} // end class    

