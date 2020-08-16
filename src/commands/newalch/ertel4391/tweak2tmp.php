<?php
/********************************************************************************
    Transfers information from 3-newalch-tweaked/ to 5-newalch-csv/
    Updates the file 4391SPO.csv with the values found in the yaml file.
    
    @license    GPL
    @history    2019-12-23 18:40:13+01:00, Thierry Graff : Creation from g5\commands\cura\muller1083\tweak2csv
********************************************************************************/
namespace g5\commands\newalch\ertel4391;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class tweak2tmp implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura <datafile> tweak2csv
        @param $params empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $report = '';
        $yamlfile = Config::$data['dirs']['edited'] . DS . 'newalch-tweaked' . DS . '4391SPO.yml';
        
        // load tweaks in an assoc arrray (keys = NR)
        $yaml = yaml_parse(file_get_contents($yamlfile));
        $tweaks = [];
        foreach($yaml as $record){
            if(!isset($record['NR'])){
                continue;
            }
            $NR = $record['NR'];
            if($NR == ''){
                continue;
            }
            unset($record['NR']);
            if(isset($tweaks[$NR])){
                return "WARNING - duplicate entry for NR = $NR in $yamlfile\n"
                     . "tweak2csv did not modify anything.\n"
                     . "Fix $yamlfile and start again.\n";
            }
            $tweaks[$NR] = $record;
        }
        
        $target = Ertel4391::loadTmpFile_nr();
        
        $keys = array_keys(current($target));
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        
        $N = 0;
        foreach($target as $NR => $row){
            if(isset($tweaks[$NR])){
                foreach($tweaks[$NR] as $k => $v){
                    if($k == G5::TWEAK_BUILD_NOTES){
                        continue;
                    }
                    if(!in_array($k, $keys)){
                        $report .= "WARNING : invalid key '$k' for NR = $NR in file $yamlfile - ignoring value\n";
                        continue;
                    }
                    $N++;
                    $row[$k] = $v; // HERE update file with tweaked value
                }
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $targetFile = Ertel4391::tmpFilename();
        file_put_contents($targetFile, $res);
        
        $report .= "Updated $N records of $targetFile\n    with tweaks of $yamlfile\n";
        return $report;
    }
    
}// end class    