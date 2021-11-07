<?php
/********************************************************************************
    Look at different aspects of cura.free.fr D10
    Not part of any build process - only to try to understand.
    
    @license    GPL
    @history    2019-11-11 18:02:47+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\D10;

use g5\app\Config;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;
use g5\commands\gauq\LERRCP;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php ertel ertel4391 examine eminence
    **/
    const POSSIBLE_PARAMS = [
        'occu',
    ];
    
    /** 
        Called by : php run-g5.php gauq D10 all
        @param $params  array containing:
                        - "D10"
                        - "look"
                        - one of self::POSSIBLE_PARAMS
        @return         Report
    **/
    public static function execute($params=[]): string{
        
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) != 3){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $method = 'look_' . $param;
        return self::$method();
    }
    
    
    // ******************************************************
    /**
        Counts the records of different occupation codes.
    **/
    public static function look_occu(){
        $csvfile = LERRCP::tmpFilename('D10');
        $res = [];
        $records = @csvAssociative::compute($csvfile);
        if(empty($records)){
            return "ERROR : missing file $csvfile\nTo build D10.csv, execute : php run-g5.php gauq D10 raw2tmp\n";
        }
        foreach($records as $record){
            $occu = $record['OCCU'];
            if(!isset($res[$occu])){
                $res[$occu] = 0;
            }
            $res[$occu]++;
        }
        $report = "Occupation codes in D10 :\n";
        foreach($res as $k => $v){
            $report .= str_pad($k, 5) . " : $v\n";
        }
        return $report;
    }
    
}// end class    

