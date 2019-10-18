<?php
/********************************************************************************
    Transfers information from 3-newalch-tweaked/ to 5-newalch-csv/
    Updates the file 1083MED.csv with the values found in the yaml file.
    
    @license    GPL
    @history    2019-10-18 16:16:16+02:00, Thierry Graff : creation from g5\transform\cura\all\tweak2csv
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\Config;
use g5\G5;
use g5\patterns\Command;

class tweak2csv implements Command{
    
    /** Key not processed by this command ; used in yaml files **/
    const BUILD_NOTES = 'build-notes';
    
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
        $yamlfile = Config::$data['dirs']['3-newalch-tweaked'] . DS . '1083MED.yml';
        
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
        
        $cura = Muller1083::loadTmpFile_nr();
        
        $keys = array_keys(current($cura));
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        
        foreach($cura as $NR => $row){
            if(isset($tweaks[$NR])){
                foreach($tweaks[$NR] as $k => $v){
                    if($k == self::BUILD_NOTES){
                        continue;
                    }
                    if(!in_array($k, $keys)){
                        $report .= "WARNING : invalid key '$k' for NR = $NR in file $yamlfile - ignoring value\n";
                        continue;
                    }
                    $row[$k] = $v; // HERE update file with tweaked value
                }
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $curafile = Config::$data['dirs']['5-newalch-csv'] . DS . '1083MED.csv';
        file_put_contents($curafile, $res);
        
        $report .= "Updated the content of 1083MED.csv with tweaks of $yamlfile\n";
        return $report;
    }
    
}// end class    
