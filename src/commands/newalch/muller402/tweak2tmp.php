<?php
/********************************************************************************
    Transfers information from
    data/build/newalch-tweak/muller-402-it-writers.yml
    to
    tmp/newalch/muller-402-it-writers.csv
    Updates the csv file with the values found in the yaml file.
    
    @license    GPL
    @history    2020-09-01 05:14:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class tweak2tmp implements Command {
    
    /** 
        @param $params empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $report = "--- muller402 tweak2tmp ---\n";
        $yamlfile = Config::$data['dirs']['build'] . DS . 'newalch-tweak' . DS . 'muller-402-it-writers.yml';
        
        // load tweaks in an assoc arrray (keys = MUID)
        $yaml = yaml_parse(file_get_contents($yamlfile));
        $tweaks = [];
        foreach($yaml as $record){
            if(!isset($record['MUID'])){
                continue;
            }
            $MUID = $record['MUID'];
            if($MUID == ''){
                continue;
            }
            unset($record['MUID']);
            if(isset($tweaks[$MUID])){
                return "WARNING - duplicate entry for MUID = $MUID in $yamlfile\n"
                     . "tweak2csv did not modify anything.\n"
                     . "Fix $yamlfile and start again.\n";
            }
            $tweaks[$MUID] = $record;
        }
        
        $target = Muller402::loadTmpFile_id();
        
        $keys = array_keys(current($target));
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        $N = 0;
        foreach($target as $MUID => $row){
            if(isset($tweaks[$MUID])){
                foreach($tweaks[$MUID] as $k => $v){
                    if($k == G5::TWEAK_BUILD_NOTES){
                        continue;
                    }
                    if(!in_array($k, $keys)){
                        $report .= "WARNING : invalid key '$k' for MUID = $MUID in file $yamlfile - ignoring value\n";
                        continue;
                    }
                    $row[$k] = $v; // HERE update file with tweaked value
                }
                $N++;
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $targetFile = Muller402::tmpFilename();
        file_put_contents($targetFile, $res);
                                             
        $report .= "Updated $N records of $targetFile\n";
        return $report;
    }
    
}// end class    
