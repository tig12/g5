<?php
/********************************************************************************
    Computes field GQID for files in data/tmp/gauq/g55/
    Useful for groups published in LERRCP booklets (not painters and priests).

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-06-05 16:38:03+02:00, Thierry Graff : creation (but not implementation)
********************************************************************************/
namespace g5\commands\gauq\g55;

use tiglib\patterns\Command;
use g5\G5;
use g5\model\DB5;
use g5\model\Person;
use g5\commands\gauq\LERRCP;

class gqid implements Command {
    
    const POSSIBLE_ACTIONS = [
        'check'     => 'Displays the possible matches between g55 and LERRCP',
        'update'    => 'Adds field GQID to tmp file',
    ];
    
    /**
        @param  $params Array containing 4 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "gqid" (useless here, used by GauqCommand).
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
                        - string 'check' or 'update'
    **/
    public static function execute($params=[]): string {
        
        $cmdSignature = 'gauq g55 gqid';
        
        $possibleParams = G55::getPossibleGroupKeys();
        $msg = "Usage : php run-g5.php $cmdSignature <group> <action>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        $msg .= "Possible values for <action>:\n";
        foreach(self::POSSIBLE_ACTIONS as $k => $v){
            $msg .= "  - $k:\t$v\n";
        }
        
        if(count($params) != 4){
            return "INVALID CALL: - this command needs exactly 2 parameters.\n$msg";
        }
        $groupKey = $params[2];
        if(!in_array($groupKey, $possibleParams)){
            return "INVALID PARAMETER: $groupKey\n$msg";
        }
        $action = $params[3];
        if(!in_array($action, array_keys(self::POSSIBLE_ACTIONS))){
            return "INVALID PARAMETER: $action\nPossible actions :\n$msg";
        }
        
        switch($action){
        	case 'check': return self::check($groupKey); break;
        	case 'update': return "--- $cmdSignature $groupKey $action ---\n" . self::update($groupKey); break;
        }
    }
    
    // ******************************************************
    /** 
        Uses G55::MATCH_LERRCP to fill column GQID in tmp file
    **/
    private static function update($groupKey) {
        
        $tmpfile = G55::tmpFilename($groupKey);
        if(!isset(G55::MATCH_LERRCP[$groupKey])){
            return "0 lines modified in $tmpfile\n";
        }
        if(!is_file($tmpfile)){
            return "UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n";
        }
        
        $N = 0;
        $res = implode(G5::CSV_SEP, G55::TMP_FIELDS) . "\n";
        foreach(G55::loadTmpFile($groupKey) as $line){
            $NUM = $line['NUM'];
            if(isset(G55::MATCH_LERRCP[$groupKey][$NUM])){
                $line['GQID'] = G55::MATCH_LERRCP[$groupKey][$NUM];
                $N++;
            }
            $res .= implode(G5::CSV_SEP, $line) . "\n";
        }
        file_put_contents($tmpfile, $res);
        return "$N lines modified in $tmpfile\n";
    }
    
    // ******************************************************
    /** 
        The report is used to build (manually) G55::MATCH_LERRCP
    **/
    private static function check($groupKey) {
        
        $report = '';
        
        $tmpPersons = []; // key = day
        $tmpfile = G55::tmpFilename($groupKey);
        if(!is_file($tmpfile)){
            return "UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n";
        }
        foreach(G55::loadTmpFile($groupKey) as $line){
            $day = substr($line['DATE'], 0, 10);
            if(!isset($tmpPersons[$day])){
                $tmpPersons[$day] = [];
            }
            $summary = $line['FNAME'] . ' ' . $line['GNAME'] . ' (' . $line['NUM'] . ') ' . $line['DATE'] . ' ' . $line['PLACE'];
            $tmpPersons[$day][] = $summary;
        }
        
        $dbPersons = []; // key = day
        $dblink = DB5::getDbLink();
        $query = "select slug,birth,partial_ids from person where partial_ids->>'" . LERRCP::SOURCE_SLUG . "'::text != 'null'";
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $birth = json_decode($row['birth'], true);
            $day = substr($row['slug'], -10);
            $ids = json_decode($row['partial_ids'], true);
            $GQID = $ids[LERRCP::SOURCE_SLUG];
            if(!isset($dbPersons[$day])){
                $dbPersons[$day] = [];
            }
            $summary = substr($row['slug'], 0, -11) . ' (' . $GQID . ') ' . ($birth['date'] != '' ? $birth['date'] : $birth['date-ut']) . ' ' . $birth['place']['name'];
            $dbPersons[$day][] = $summary;
        }
        
        foreach($tmpPersons as $day => $p1s){
            if(isset($dbPersons[$day])){
                $report .= "\n";
                foreach($p1s as $p1){
                    $report .= "1 $p1\n";
                }
                foreach($dbPersons[$day] as $p2){
                    $report .= "2 $p2\n";
                }
            }
        }
        return $report;
    }
    
} // end class
