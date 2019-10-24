<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-10-23 22:55:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

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
            $possibleParams_str .= "  '- $k' : $v\n";
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
        
        $method = "export_$param";
        return self::$method();
    }
    
    
    // ******************************************************
    /**
        Exports to 9-output/
    **/
    private static function export_dl(){
        $infile = Config::$data['dirs']['5-newalch-csv'] . DS . Muller1083::TMP_CSV_FILE;
        $outfile = Config::$data['dirs']['9-newalch'] . DS . Muller1083::TMP_CSV_FILE;
        copy($infile, $outfile);
        return "Copied $infile to $outfile\n";
    }
    
    
}// end class
