<?php
/********************************************************************************
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-11-24 02:52:44+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

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
            
            Config::$data['dirs']['5-csicop'] . DS . '181-csicop-si42.csv'=>
            Config::$data['dirs']['9-csicop'] . DS . '181-csicop-si42.csv',
            
            Config::$data['dirs']['5-csicop'] . DS . '408-csicop-si42.csv'=>
            Config::$data['dirs']['9-csicop'] . DS . '408-csicop-si42.csv',
            
            //Config::$data['dirs']['5-csicop'] . DS . 'CSICOP-408.csv'=>
            //Config::$data['dirs']['9-csicop'] . DS . 'CSICOP-408.csv',
            
        ];
        foreach($gen as $in => $out){
            copy($in, $out);
            $report .= "Copied $in to $out\n";
        }
        return $report;
    }
    
    
}// end class
