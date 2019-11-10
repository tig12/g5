<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-10-23 23:54:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class export implements Command {
    
    const POSSIBLE_PARAMS = [
        'dl' => "Export to 9-output/",
    ];
    
    // *****************************************
    /** 
        @param $params  Array containing one string
                        Must be one of self::POSSIBLE_PARAMS
        @return         Report
    **/
    public static function execute($params=[]): string{
        
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  $k : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING - This function needs one parameter\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER : $param\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $method = "export_$param";
        return self::$method();
    }
    
    
    // ******************************************************
    /**
        Exports to 9-output/
    **/
    private static function export_dl(){
        $report = '';
        $gen = [
            Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::TMP_CSV_FILE =>
            Config::$data['dirs']['9-newalch'] . DS . Ertel4391::TMP_CSV_FILE,
            
            Config::$data['dirs']['5-cpara'] . DS . '535-cpara.csv' =>
            Config::$data['dirs']['9-cpara'] . DS . '535-cpara.csv',
            
            Config::$data['dirs']['5-cpara'] . DS . '611-cpara-full.csv'=>
            Config::$data['dirs']['9-cpara'] . DS . '611-cpara-full.csv',
            
            Config::$data['dirs']['5-cpara'] . DS . '76-cpara-lowers.csv'=>
            Config::$data['dirs']['9-cpara'] . DS . '76-cpara-lowers.csv',
            
            Config::$data['dirs']['5-csicop'] . DS . '190-csicop.csv'=>
            Config::$data['dirs']['9-csicop'] . DS . '190-csicop.csv',
            
            Config::$data['dirs']['5-cfepp'] . DS . '925-cfepp.csv'=>
            Config::$data['dirs']['9-cfepp'] . DS . '925-cfepp.csv',
        ];
        foreach($gen as $in => $out){
            copy($in, $out);
            $report .= "Copied $in to $out\n";
        }
        return $report;
    }
    
    
}// end class
