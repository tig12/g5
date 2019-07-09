<?php
/********************************************************************************
    Uses 5-newalch-csv/1083MED.csv to compute missing names in 5-cura-csv/A2.csv
    
    Operates only for file A2
    
    @pre        5-cura-csv/A2.csv must exist.
                So src/transform/cura/A/raw2csv.php must have been executed before.
    @pre        5-newalch-csv/1083MED.csv must exist.
                So src/transform/newalch/mullel1083/raw2csv.php must have been executed before.
    @license    GPL
    @history    2019-07-09 08:41:06+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;

class muller2csv implements Command{
    
    const POSSIBLE_PARAM = [
        'update',
        'echo'
    ];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura A2 muller2csv [update|echo]
        @param $params array that must contain 3 elements :
                       - datafile : must be 'A2'
                       - command : useless
                       - one param containing one of POSSIBLE_PARAM element
    **/
    public static function execute($params=[]): string{
        
        $ex_msg = "php run-g5.php cura A2 muller2csv update\n";
        $err_msg = "WRONG USAGE - muller2csv needs one parameter. Can be :\n"
                 . "  update : modify cura file\n"
                 . "  echo : echo detailed names\n"
                 . "ex : $ex_msg";
        
        if(count($params) != 3){
            return $err_msg;
        }
        if($params[0] != 'A2'){
            return "WRONG PARAMETER - muller2csv must be called with datafile = 'A2'\nex : $ex_msg";
        }
        
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAM)){
            return $err_msg;
        }
        
        $report = '';
        
        $curaRows = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . 'A2.csv');
        $mullerRows = csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . '1083MED.csv');
        
        // build $cura_missing - assoc array with NUM as key
        $curaMissing = [];
        foreach($curaRows as $row){
            if(strpos($row['FNAME'], 'Gauquelin-A2-') === 0){
                $NUM = $row['NUM'];
                $curaMissing[$NUM] = $row;
            }
        }
        ksort($curaMissing);
        $keys_curaMissing = array_keys($curaMissing);
        $report .= 'Nb missing names in cura A2 : ' . count($curaMissing) . "\n";
        
        // load lines with A2 reference in muller file
        // assoc array with GNR (= NUM in cura) as key
        $mullerA2 = [];
        foreach($mullerRows as $row){
            if(substr($row['GNR'], 0, 3) == 'SA2'){
                $num = substr($row['GNR'], 3);
                $mullerA2[$num] = $row;
            }
        }
        ksort($mullerA2);
        $keys_mullerA2 = array_keys($mullerA2);
        $report .= 'Müller 1083 contains : ' . count($mullerA2) . " lines from A2\n"; 
        
//echo "\n<pre>"; print_r($curaMissing); echo "</pre>\n"; exit;
        //
        // Action
        //
        
        $nCorrected = 0;
        
        // Update mode - modify A2.csv
        if($param == 'update'){
            $res = implode(G5::CSV_SEP, array_keys($curaRows[0])) . "\n";
            foreach($curaRows as $row){
                $NUM = $row['NUM'];
                if(in_array($NUM, $keys_mullerA2) && in_array($NUM, $keys_curaMissing)){
                    $tmp = $curaMissing[$NUM];
                    $tmp['FNAME'] = $mullerA2[$NUM]['FNAME'];
                    $tmp['GNAME'] = $mullerA2[$NUM]['GNAME'];
                    $res .= implode(G5::CSV_SEP, $tmp) . "\n";
                    $nCorrected++;
                }
                else{
                    $res .= implode(G5::CSV_SEP, $row) . "\n";
                }
            }
            $outfile = Config::$data['dirs']['5-cura-csv'] . DS . 'A2.csv';
            file_put_contents($outfile, $res);
        }
        
        // Echo mode - use for visual check that birth dates match
        else if($param == 'echo'){
            foreach($curaMissing as $num => $curaRow){
                if(isset($mullerA2[$num])){
                    $mullerRow =& $mullerA2[$num];
                    echo "\n" . $num . ' ' . $mullerRow['FNAME'] . ' ' . $mullerRow['GNAME'] . "\n";
                    echo 'A2 date : ' . $curaRow['DATE'] . "\n";
                    echo 'A2 corr : ' . $curaRow['DATE_C'] . "\n";
                    echo 'Muller  : ' . $mullerRow['DATE'] . "\n";
                    $nCorrected++;
                }
            }
            echo "\nMuller keys : " . implode(' ', $keys_mullerA2) . "\n";
            echo "\nA2 missing keys : " . implode(' ', $keys_curaMissing) . "\n";
            echo "\nCommon keys : " . implode(' ', array_intersect($keys_mullerA2, $keys_curaMissing)) . "\n";
        }
        
        $report .= "Nb corrections : $nCorrected\n";
        
        return $report;
    }
    
}// end class    

