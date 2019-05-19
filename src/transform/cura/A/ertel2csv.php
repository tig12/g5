<?php
/********************************************************************************
    Uses 5-tmp/newalch/4391SPO.csv to compute missing names in 5-tmp/cura-csv/A1.csv
    
    Works only for file A1
    
    @pre        5-tmp/cura-csv/A1.csv must exist.
                So src/transform/cura/A/raw2csv.php must have been executed before.
    @pre        5-tmp/newalch/4391SPO.csv must exist.
                So src/transform/newalch/ertel4391/raw2csv.php must have been executed before.
    @license    GPL
    @history    2019-05-18 07:06:41+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\init\Config;
use g5\patterns\Command;
//use g5\transform\cura\Cura;

class ertel2csv implements Command{
    
    const POSSIBLE_PARAM = ['update', 'echo'];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run.php cura A1 ertel2csv [update|echo]
        @param $params array that must contain 3 elements :
                       - datafile : must be 'A1'
                       - command : useless
                       - one param containing one of POSSIBLE_PARAM element
    **/
    public static function execute($params=[]): string{
        
        $ex_msg = "php run.php cura A1 ertel2csv update\n";
        $err_msg = "WRONG USAGE - ertel2csv needs one parameter. Can be 'update' (modify cura file) or 'echo' (echo detailed names)\nex : $ex_msg";
        
        if(count($params) != 3){
            return $err_msg;
        }
        if($params[0] != 'A1'){
            return "WRONG PARAMETER - ertel2csv must be called with datafile = 'A1'\nex : $ex_msg";
        }
        
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAM)){
            return $err_msg;
        }
        
        $report = '';
        
        $curaRows = \lib::csvAssociative(Config::$data['dirs']['5-cura-csv'] . DS . 'A1.csv');
        $erteRows = \lib::csvAssociative(Config::$data['dirs']['5-newalch-csv'] . DS . '4391SPO.csv');
        
        // build $cura_missing and $cura_all
        // assoc arrays with NUM as key
        $curaMissing = [];
        foreach($curaRows as $row){
            if(strpos($row['FNAME'], 'Gauquelin-A1-') === 0){
                $NUM = $row['NUM'];
                $curaMissing[$NUM] = $row;
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
        if($param == 'update'){
            $res = implode(Config::$data['CSV_SEP'], array_keys($curaRows[0])) . "\n";
            $missingNums = array_keys($curaMissing);
            foreach($curaRows as $row){
                $NUM = $row['NUM'];
                if(in_array($NUM, $missingNums)){
                    $tmp = $curaMissing[$NUM];
                    $tmp['FNAME'] = $ertelA1[$NUM]['FNAME'];
                    $tmp['GNAME'] = $ertelA1[$NUM]['GNAME'];
                    $res .= implode(Config::$data['CSV_SEP'], $tmp) . "\n";
                    $nCorrected++;
                }
                else{
                    $res .= implode(Config::$data['CSV_SEP'], $row) . "\n";
                }
            }
            $outfile = Config::$data['dirs']['5-cura-csv'] . DS . 'A1.csv';
            file_put_contents($outfile, $res);
        }
        
        // Echo mode - use for visual check that birth dates match
        else if($param == 'echo'){
            foreach($curaMissing as $num => $curaRow){
                if(isset($ertelA1[$num])){
                    $ertelRow =& $ertelA1[$num];
                    echo "\n" . $num . ' ' . $ertelRow['FNAME'] . ' ' . $ertelRow['GNAME'] . "\n";
                    echo $curaRow['DATE'] . "\n" . $ertelRow['DATE'] . "\n";
                    $nCorrected++;
                }
            }
        }
        
        $report .= "Nb corrections : $nCorrected\n";
        
        return $report;
    }
    
}// end class    

