<?php
/********************************************************************************
    Transfers information from data/db/init/newalch-tweak/ to data/tmp/ertel/
    Updates the file data/tmp/ertel/ertel-4384-athletes.csv with the values found in the yaml tweak file.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-12-23 18:40:13+01:00, Thierry Graff : Creation from command muller1083 tweak2csv
********************************************************************************/
namespace g5\commands\ertel\sport;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class tweak2tmp implements Command {
    
    /** 
        Called by : php run-g5.php ertel sport tweak2tmp
        @param $params empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $report = "--- ertel sport tweak2tmp ---\n";
        $yamlfile = ErtelSport::tweakFilename();
        
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
        
        $target = ErtelSport::loadTmpFile_nr();
        
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
                    $row[$k] = $v; // HERE update file with tweaked value
                }
                $N++;
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $targetFile = ErtelSport::tmpFilename();
        file_put_contents($targetFile, $res);
        
        $report .= "Updated $N records in $targetFile\n";
        return $report;
    }
    
}// end class    
