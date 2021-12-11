<?php
/********************************************************************************
    Transfers information from data/db/init/newalch-tweak/
    to data/tmp/muller/5-medics/muller5-1083-medics.csv
    Updates the file muller5-1083-medics.csv with the values found in the yaml file.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-10-18 16:16:16+02:00, Thierry Graff : creation from g5\commands\gauq\all\tweak2csv
********************************************************************************/
namespace g5\commands\muller\m5medics;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class tweak2tmp implements Command {
    
    /** 
        Called by : php run-g5.php muller m5medics tweak2csv
        @param $params empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $report = "--- muller m5medics tweak2tmp ---\n";
        $yamlfile = Config::$data['dirs']['init'] . DS . 'newalch-tweak' . DS . 'muller5-1083-medics.yml';
        
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
        
        $target = M5medics::loadTmpFile_nr();
        
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
        
        $targetFile = M5medics::tmpFilename();
        file_put_contents($targetFile, $res);
                                             
        $report .= "Updated $N records of $targetFile\n";
        return $report;
    }
    
}// end class    
