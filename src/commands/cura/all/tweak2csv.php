<?php
/********************************************************************************
    Transfers information from 3-cura-tweaked/ to 5-cura-csv/
    Updates a file of 5-cura-csv/ with the values found in the yaml file.
    
    WARNING : this step must be done at an early stage.
    For example, if a date or a time is modified, should be done before step "legalTime"
    
    @license    GPL
    @history    2019-07-26 16:58:21+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\all;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;

class tweak2csv implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura <datafile> tweak2csv
        @param $params array containing two strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here)
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $report = '';
        $datafile = $params[0];
        $yamlfile = Config::$data['dirs']['3-cura-tweaked'] . DS . $datafile . '.yml';
        
        if(!is_file($yamlfile)){
            return "Missing file $yamlfile\n"
                 . "tweak2csv did not modify anything.\n";
        }
        
        // load tweaks in an assoc arrray (keys = NUM)
        $yaml = yaml_parse(file_get_contents($yamlfile));
        $tweaks = [];
        foreach($yaml as $record){
            if(!isset($record['NUM'])){
                continue;
            }
            $NUM = $record['NUM'];
            if($NUM == ''){
                continue;
            }
            unset($record['NUM']);
            if(isset($tweaks[$NUM])){
                return "WARNING - duplicate entry for NUM = $NUM in $yamlfile\n"
                     . "tweak2csv did not modify anything.\n"
                     . "Fix $yamlfile and start again.\n";
            }
            $tweaks[$NUM] = $record;
        }
        
        $cura = Cura::loadTmpCsv_num($datafile);
        
        $keys = array_keys(current($cura));
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        
        foreach($cura as $NUM => $row){
            if(isset($tweaks[$NUM])){
                foreach($tweaks[$NUM] as $k => $v){
                    if($k == G5::TWEAK_BUILD_NOTES){
                        continue;
                    }
                    if(!in_array($k, $keys)){
                        $report .= "WARNING : invalid key '$k' for NUM = $NUM in file $yamlfile - ignoring value\n";
                        continue;
                    }
                    $row[$k] = $v; // HERE update cura with tweaked value
                }
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $curafile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        file_put_contents($curafile, $res);
        
        $report .= "Updated the content of $curafile with tweaks of $yamlfile\n";
        return $report;
    }
    
}// end class    
