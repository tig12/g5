<?php
/********************************************************************************
    Transfers information from data/edited/cura-tweaked/ to g5 db
    
    WARNING : this step must be done at an early stage.
    For example, if a date or a time is modified, should be done before step "legalTime"
    
    @license    GPL
    @history    2019-07-26 16:58:21+02:00, Thierry Graff : creation
    @history    2020-08-11 14:50:35+02:00, Thierry Graff : adaptation to g5 db
********************************************************************************/
namespace g5\commands\cura\all;

use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Group;
use g5\model\Person;

class tweak2db implements Command{
    
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
        
        // load data from db - build associative array with NUM as key
        $tmp = Group::loadWithMembers($datafile); // $datafile is the group slug
        $cura = [];
        foreach($tmp as $p){
            $cura[$p->data['ids_in_sources'][$datafile]] = $p;
        }
        
        $nUpdated = 0;
        foreach($tweaks as $NUM => $tweak){
            $recomputeSlug = false;
            if($NUM == ''){
                continue;
            }
            if(isset($tweak['FNAME'])){
                $cura[$NUM]->data['name']['family'] = trim($tweak['FNAME']);
                $recomputeSlug = true;
            }
            if(isset($tweak['GNAME'])){
                $cura[$NUM]->data['name']['given'] = trim($tweak['GNAME']);
                $recomputeSlug = true;
            }
            if(isset($tweak['DATE'])){
                $cura[$NUM]->data['birth']['date'] = trim($tweak['DATE']);
                $recomputeSlug = true;
            }
            if(isset($tweak['C3'])){
                $cura[$NUM]->data['birth']['place']['c3'] = trim($tweak['C3']);
            }
            if($recomputeSlug){
                $cura[$NUM]->computeSlug();
            }
            $nUpdated++;
            Person::update($cura[$NUM]);
        }
        $report .= "Updated $nUpdated persons with tweaks of $tweaksFile\n";
        return $report;
    }
    
}// end class    
