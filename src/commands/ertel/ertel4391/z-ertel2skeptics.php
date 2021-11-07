<?php
/********************************************************************************
    Computes csv files for Comité Para, CSICOP, CFEPP
    Input files :
        - data/tmp/ertel/ertel-4384-athletes.csv
        - data/tmp/gauq/lerrcp/A1.csv
    Output files : in output/history directory ; see POSSIBLE_PARAMS
    
    TODO command to replace by an export computed from db.
    
    @license    GPL
    @history    2019-06-05 23:25:56+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel\ertel4391;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;
use g5\commands\gauq\LERRCP;
use g5\commands\cpara\CPara;
use g5\commands\csicop\CSICOP;
use g5\commands\cfepp\CFEPP;

class ertel2skeptics implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 ertel2skeptics cfepp
    **/
    const POSSIBLE_PARAMS = [
        'all'           => 'Generate all skeptic files',
        'cpara'         => 'Generate 5-cpara/535-cpara-ertel.csv',
        'cpara-full'    => 'Generate 5-cpara/611-cpara-full-ertel.csv',
        'cpara-lowers'  => 'Generate 5-cpara/76-cpara-lowers-ertel.csv',
        'cfepp'         => 'Generate 5-cfepp/925-cfepp-ertel.csv',
        'csicop'        => 'Generate 5-csicop/192-csicop-ertel.csv',
    ];
    
    /** 
        Contents of data/tmp/gauq/lerrcp/A1.csv
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
        
        $possibleParams_str = ''; 
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= '  ' . str_pad($k, 12) . " : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        // init $do* vars
        $docp = $docpfull = $docplowers = $docsicop = $docfepp = false;
        if($param == 'all'){
            $docp = $docpfull = $docplowers = $docsicop = $docfepp = true;
        }
        else{
            if($param == 'cpara') $docp = true;
            if($param == 'cpara-full') $docpfull = true;
            if($param == 'cpara-lowers') $docplowers = true;
            if($param == 'csicop') $docsicop = true;
            if($param == 'cfepp') $docfepp = true;
        }
        
        // initialize self::$curaA1
        self::$curaA1 = LERRCP::loadTmpFile_num('A1');
        
        // build arrays
        $cp = $cpfull = $cplowers = $csicop = $cfepp = [];
        $rows = Ertel4391::loadTmpFile());
        foreach($rows as $row){
            $NUM = $row['G_NR'];
            if(($docp || $docpfull || $docplowers) && $row['PARA_NR'] != ''){
                $cpfull[] = $row;
                if($row['QUEL'] == 'GCPAR'){
                    $cplowers[] = $row;
                }
                else{
                    $cp[] = $row;
                }
            }
            if($docsicop && $row['CSINR'] != ''){
                $csicop[] = $row;
            }
            if($docfepp && $row['CFEPNR'] != ''){
                $cfepp[] = $row;
            }
        }
        
        // output
        if($docp){
            $res = self::array2csv($cp);
            $out = CPara::outputDirname() . DS . '535-cpara-ertel.csv';
            file_put_contents($out, $res);
            $report .= "CPARA : " . count($cp) . " records saved - stored in $out\n";
        }
        if($docpfull){
            $res = self::array2csv($cpfull);
            $out = CPara::outputDirname() . DS . 'cpara-ertel-611-full.csv';
            file_put_contents($out, $res);
            $report .= "CPARA full : " . count($cpfull) . " records saved - stored in $out\n";
        }
        if($docplowers){
            $res = self::array2csv($cplowers);
            $out = CPara::outputDirname() . DS . 'cpara-ertel-76-lowers.csv';
            file_put_contents($out, $res);
            $report .= "CPARA lowers : " . count($cplowers) . " records saved - stored in $out\n";
        }
        if($docsicop){
            $res = self::array2csv($csicop);
            $out = CSICOP::outputDirname() . DS . 'csicop-ertel-192.csv';
            file_put_contents($out, $res);
            $report .= "CSICOP : " . count($csicop) . " records saved - stored in $out\n";
        }
        if($docfepp){
            $res = self::array2csv($cfepp);
            $out = CFEPP::outputDirname() . DS . 'cfepp-ertel-925.csv';
            file_put_contents($out, $res);
            $report .= "CFEPP : " . count($cfepp) . " records saved - stored in $out\n";
        }
        return $report;
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
