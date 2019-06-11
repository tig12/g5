<?php
/********************************************************************************
    Computes groups of Comité Para, CSICOP, CFEPP
    Ids come from 4391SPO.csv and other fields from A1.csv
    Input files :
    - 5-tmp/newalch-csv/4391SPO.csv
    - 5-tmp/cura-csv/A1.csv
    Output files :
    - 5-tmp/cp/611-from-ertel.csv
    - 5-tmp/csicop/190-from-ertel.csv
    - 5-tmp/cfepp/925-from-ertel.csv
    
    To add a new function : 
        - add entry in POSSIBLE_PARAMS
        - implement a method named "examine_<entry>"
    
    @license    GPL
    @history    2019-06-05 23:25:56+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;
                                     
class ertel2skeptics implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 ertel2skeptics cfepp
    **/
    const POSSIBLE_PARAMS = [
        'ALL',
        'cp',
        'cfepp',
        'csicop',
    ];
    
    /** 
        Contents of 5-tmp/cura-csv/A1.csv
        Associative array ; keys = Gauquelin NUM
    **/
    private static $curaA1 = [];
    
    // *****************************************
    /** 
        Computes the 3 output files.
        @param $param   Array containing one element (a string)
                        Must be one of self::POSSIBLE_PARAMS
        @return         Report
    **/
    public static function execute($params=[]): string{
        
        $report = '';
        
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) == 0){
            return "PARAMETER MISSING in g5\\transform\\newalch\\ertel4391\\ertel2skeptics\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER in g5\\transform\\newalch\\ertel4391\\ertel2skeptics\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        
        // init $do* vars
        $docp = $docsicop = $docfepp = false;
        if($param == 'ALL'){
            $docp = $docsicop = $docfepp = true;
        }
        else{
            if($param == 'cp') $docp = true;
            if($param == 'csicop') $docsicop = true;
            if($param == 'cfepp') $docfepp = true;
        }
        
        // initialize self::$curaA1
        $tmp = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . 'A1.csv');
        foreach($tmp as $row){
            self::$curaA1[$row['NUM']] = $row;
        }
        
        // build arrays
        $cp = $csicop = $cfepp = [];
        $rows = csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::TMP_CSV_FILE);
        foreach($rows as $row){
            $NUM = $row['G_NR'];
            if($docp && $row['PARA_NR']){
                $cp[] = self::fill_line('CP_ID', $row['PARA_NR'], $NUM);
            }
            if($docsicop && $row['CSINR']){
                $csicop[] = self::fill_line('CSI_ID', $row['CSINR'], $NUM);
            }
            if($docfepp && $row['CFEPNR']){
                $cfepp[] = self::fill_line('CFE_ID', $row['CFEPNR'], $NUM);
            }
        }
        
        // output
        if($docp){
            $res = self::array2csv($cp);
            $out = Config::$data['dirs']['5-cp'] . DS . '611-from-ertel.csv';
            file_put_contents($out, $res);
            echo "CP : " . count($cp) . " records saved - stored in $out\n";
        }
        if($docsicop){
            $res = self::array2csv($csicop);
            $out = Config::$data['dirs']['5-csicop'] . DS . '190-from-ertel.csv';
            file_put_contents($out, $res);
            echo "CSICOP : " . count($csicop) . " records saved - stored in $out\n";
        }
        if($docfepp){
            $res = self::array2csv($cfepp);
            $out = Config::$data['dirs']['5-cfepp'] . DS . '925-from-ertel.csv';
            file_put_contents($out, $res);
            echo "CFEPPP : " . count($cfepp) . " records saved - stored in $out\n";
        }
        return '';
    }
    
    
    // ******************************************************
    /**
        @param $key     Name of the key of $res where the skeptics' id is stored.
        @param $value   Value stored in $key
        @param $NUM     Gauquelin id, field NUM of cura files.
        @return         Assoc array = to a record of A1.csv with one more field added as first key
    **/
    private static function fill_line($key, $value, $NUM){
        return [$key => $value] + self::$curaA1[$NUM];
    }
    
    
    // ******************************************************
    /**
        @param $a   Associative array
        @return     A string expressing $a to be stored in a csv file
    **/
    private static function array2csv($a){
        $keys = array_keys($a[0]);
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        foreach($a as $row){
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        return $res;
    }
    
}// end class
