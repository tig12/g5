<?php
/******************************************************************************
    generates lists of profession codes and their names
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-05-03 10:29:04+02:00, Thierry Graff : Creation
    @history    2019-05-28 16:06:12+02:00, Thierry Graff : Replace csv version by yaml
    @history    2020-09-05 17:00:55+02:00, Thierry Graff : Integrate to commands/db/look
********************************************************************************/

namespace g5\commands\db\look;

use g5\patterns\Command;
use g5\Config;
use g5\model\Occupation;

class occu implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $codes = self::readInputFile();    
        $res = '';
        // General codes
        $res .= "\n<!-- ************************************* -->\n";
        $res .= "<h3>General codes</h3>\n";
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "<tr><th>Code</th><th>Label (fr)</th><th>Label (en)</th></tr>\n";
        foreach($codes as $code => $record){
            $belongs = self::belongsTo($record);
            if(!empty($belongs)){
                continue;
            }
            $res .= "<tr><td>$code</td><td>{$record['fr']}</td><td>{$record['en']}</td></tr>\n";
        }
        $res .= "</table>\n";
        // Artists
        $res .= "\n<!-- ************************************* -->\n";
        $res .= "<h3>Artists</h3>\n";
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "<tr><th>Code</th><th>Label (fr)</th><th>Label (en)</th></tr>\n";
        foreach($codes as $code => $record){
            $belongs = self::belongsTo($record);
            if(!in_array('AR', $belongs)){
                continue;
            }
            $res .= "<tr><td>$code</td><td>{$record['fr']}</td><td>{$record['en']}</td></tr>\n";
        }
        $res .= "</table>\n";
        // Sportsmen
        $res .= "\n<!-- ************************************* -->\n";
        $res .= "<h3>Sports</h3>\n";
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "<tr><th>Code</th><th>Label (fr)</th><th>Label (en)</th></tr>\n";
        foreach($codes as $code => $record){
            $belongs = self::belongsTo($record);
            if(!in_array('SP', $belongs)){
                continue;
            }
            $res .= "<tr><td>$code</td><td>{$record['fr']}</td><td>{$record['en']}</td></tr>\n";
        }
        $res .= "</table>\n";
        return $res;
    }
    
    //
    // Auxiliary functions
    //
    
    // ******************************************************
    /**
        Loads file occu.yml
        If a code appears more than once, the program exits with an error message
        Otherwise, file supposed correct, no check done
        @return associative array profession code => content of this record
    **/
    private static function readInputFile(){
        $records = yaml_parse_file(Occupation::getBuildFile());
        $res = [];
        $check = [];
        foreach($records as $rec){
            $code = $rec['code'];
            if(!isset($check[$code])){
                $check[$code] = 0;
            }
            $check[$code]++;
            $res[$code] = $rec;
        }
        // check doublons
        foreach($check as $code => $n){
            if($n > 1){
                die("ERROR : code '$code' appears more than once\n");
            }
        }
        //
        ksort($res);
        return $res;
    }
    
    
    // ******************************************************
    /**
        @param $record One element of the yaml file, representing an occupation
    **/
     private static function belongsTo($record){
         return $record['belongs-to'] ?? [];
    }

} // end class
