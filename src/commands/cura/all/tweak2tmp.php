<?php
/********************************************************************************
    Transfers information from data/edited/cura-tweaked/ to data/tmp/cura/
    Updates a file of data/tmp/cura/ with the values found in the yaml file containing tweaks.
    
    WARNING : this step must be done just after step raw2tmp.
    For example, if a date or a time is modified, should be done before step "legalTime"
    
    @license    GPL
    @history    2019-07-26 16:58:21+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\all;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Group;
use g5\model\Person;

class tweak2tmp implements Command{
    
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
        $tweaksFile = Config::$data['dirs']['edited'] . DS . 'cura-tweaked' . DS . $datafile . '.yml';
        
        if(!is_file($tweaksFile)){
            return "Missing file $tweaksFile\n"
                 . "tweak2csv did not modify anything.\n";
        }
        
        // load tweaks in an assoc arrray (keys = NUM)
        $yaml = yaml_parse(file_get_contents($tweaksFile));
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
                return "WARNING - duplicate entry for NUM = $NUM in $tweaksFile\n"
                     . "tweak2csv did not modify anything.\n"
                     . "Fix $tweaksFile and start again.\n";
            }
            $tweaks[$NUM] = $record;
        }
        
        // modify file in data/tmp/cura
        
        $cura = Cura::loadTmpFile_num($datafile);
        $keys = array_keys(current($cura));
        $res = implode(G5::CSV_SEP, $keys) . "\n";
        $nUpdated = 0;
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
                $nUpdated++;
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $curafile = Cura::tmpFilename($datafile);
        file_put_contents($curafile, $res);
        $report .= "Updated $nUpdated persons with tweaks of $tweaksFile\n";
        return $report;
    }
    
}// end class    