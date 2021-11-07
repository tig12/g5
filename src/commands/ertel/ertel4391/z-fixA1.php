<?php
/********************************************************************************
    Uses 5-newalch-csv/4391SPO.csv to compute missing names in 5-cura-csv/A1.csv
    
    Operates only for file A1
    
    @pre        5-cura-csv/A1.csv must exist.
                So src/commands/cura/A/raw2csv.php must have been executed before.
    @pre        5-newalch-csv/4391SPO.csv must exist.
                So src/commands/newalch/ertel4391/raw2csv.php must have been executed before.
    @license    GPL
    @history    2019-05-18 07:06:41+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel\ertel4391;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;

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
        
        $report = '';
        
        $curaRows = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . 'A1.csv');
        $erteRows = csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . '4391SPO.csv');
        
        // build $cura_missing and $cura_all
        // assoc arrays with NUM as key
        $curaMissing = [];
        foreach($curaRows as $row){
            if(strpos($row['FNAME'], 'Gauquelin-A1-') === 0){
                $curaMissing[$row['NUM']] = $row;
            }
        }
        $report .= 'Nb missing names in cura A1 : ' . count($curaMissing) . "\n";
        
        // load lines with A1 reference in ertel file
        // assoc array with G_NR (= NUM in cura) as key
        $ertelA1 = [];
        foreach($erteRows as $row){
            if(substr($row['QUEL'], 0, 5) == 'G:A01'){
                $gnr = $row['G_NR'];
                $ertelA1[$row['G_NR']] = $row;
            }
        }
        $report .= 'Ertel 4391 contains : ' . count($ertelA1) . " lines from A1\n"; 
        
        //
        // Action
        //
        
        $nCorrected = 0;
        
        // Update mode - modify A1.csv
        if($action == 'update'){
            $res = implode(G5::CSV_SEP, array_keys($curaRows[0])) . "\n";
            $missingNums = array_keys($curaMissing);
            foreach($curaRows as $row){
                $NUM = $row['NUM'];
                if(in_array($NUM, $missingNums)){
                    $tmp = $curaMissing[$NUM];
                    $tmp['FNAME'] = $ertelA1[$NUM]['FNAME'];
                    $tmp['GNAME'] = $ertelA1[$NUM]['GNAME'];
                    $res .= implode(G5::CSV_SEP, $tmp) . "\n";
                    $nCorrected++;
                }
                else{
                    $res .= implode(G5::CSV_SEP, $row) . "\n";
                }
            }
            $outfile = Config::$data['dirs']['5-cura-csv'] . DS . 'A1.csv';
            file_put_contents($outfile, $res);
        }
        
        // Report mode - use for visual check that birth dates match
        else if($action == 'report'){
            foreach($curaMissing as $num => $curaRow){
                if(isset($ertelA1[$num])){
                    $ertelRow =& $ertelA1[$num];
                    $report .= "\n" . $num . ' ' . $ertelRow['FNAME'] . ' ' . $ertelRow['GNAME'] . "\n";
                    $report .= $curaRow['DATE'] . "\n" . $ertelRow['DATE'] . "\n";
                    $nCorrected++;
                }
            }
        }
        
        $report .= "Nb corrections : $nCorrected\n";
        
        return $report;
    }
    
}// end class    

