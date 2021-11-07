<?php
/********************************************************************************
    Transfers information from data/build/cura-tweak/ to data/tmp/gauq/lerrcp
    Updates a file of data/tmp/gauq/lerrcp/ with the values found in the yaml file containing tweaks.
    
    WARNING : this step must be done just after step raw2tmp.
    For example, if a date or a time is modified, should be done before step "legalTime"
    
    @license    GPL
    @history    2019-07-26 16:58:21+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\all;

use g5\G5;
use g5\app\Config;
use g5\commands\gauq\LERRCP;
use g5\model\Group;
use g5\model\Person;
use tiglib\patterns\Command;

class tweak2tmp implements Command {
    
    /** 
        Called by : php run-g5.php gauq <datafile> tweak2tmp
        @param $params array containing two strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here)
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $datafile = $params[0];
        $tweaksFile = Config::$data['dirs']['init'] . DS . 'cura-tweak' . DS . $datafile . '.yml';
        
        $report = "--- $datafile tweak2tmp ---\n";
        
        if(!is_file($tweaksFile)){
            $report .= "Missing file $tweaksFile - nothing was modified.\n";
            return $report;
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
                $report .= "WARNING - duplicate entry for NUM = $NUM in $tweaksFile\n"
                     . "tweak2tmp did not modify anything.\n"
                     . "Fix $tweaksFile and start again.\n";
                 return $report;
            }
            $tweaks[$NUM] = $record;
        }
        
        // modify file in data/tmp/gauq/lerrcp
        
        $cura = LERRCP::loadTmpFile_num($datafile);
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
                        $report .= "WARNING : invalid key '$k' for NUM = $NUM in file $tweaksFile - ignoring value\n";
                        continue;
                    }
                    $row[$k] = $v; // HERE update cura with tweaked value
                }
                $nUpdated++;
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        
        $curafile = LERRCP::tmpFilename($datafile);
        file_put_contents($curafile, $res);
        $report .= "Updated $nUpdated records of $curafile with $tweaksFile\n";
        return $report;
    }
    
}// end class    
