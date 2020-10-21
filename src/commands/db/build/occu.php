<?php
/******************************************************************************
    Builds data/model/occu.yml from data/build/occu.yml
    
    @license    GPL
    @history    2020-10-21 02:23:12+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\build;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;
use g5\model\Occupation;

class occu implements Command {
    
    // *****************************************
    /** 
        @param  $params empty array
    **/
    public static function execute($params=[]): string { // Command Implementation
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $records = yaml_parse_file(Occupation::getBuildFile());
        $res = [];
        $check = [];
        foreach($records as $rec){
echo "\n"; print_r($rec); echo "\n"; continue;
            $code = $rec['code'];
            if(!isset($check[$code])){
                $check[$code] = 0;
            }
            $check[$code]++;
            $res[$code] = $rec;
        }
exit;
        // check doublons
        foreach($check as $code => $n){
            if($n > 1){
                die("ERROR : code '$code' appears more than once\n");
            }
        }
        //
        ksort($res);

        $report = '';
        return $report;
    }
    
    
}// end class
