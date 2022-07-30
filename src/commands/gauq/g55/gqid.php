<?php
/********************************************************************************
    Computes field GQID for files in data/tmp/gauq/g55/

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-06-05 16:38:03+02:00, Thierry Graff : creation (but not implementation)
********************************************************************************/
namespace g5\commands\gauq\g55;

use tiglib\patterns\Command;
use g5\G5;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use g5\commands\gauq\LERRCP;
use tiglib\strings\slugify;

class gqid implements Command {
    
    const POSSIBLE_ACTIONS = [
        'cache'     => 'Computes slug => GQID associations from db and stores it on disk',
        'check'     => 'Displays the possible matches between g55 and LERRCP',
        'update'    => 'Adds field GQID to tmp file',
    ];
    
    /**
        Usage of this command:
            php run-g5.php gauq g55 gqid cache
            php run-g5.php gauq g55 gqid 01-576-physicians check
    
        @param  $params Array containing 4 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "gqid" (useless here, used by GauqCommand).
                        - a string identifying what is processed (ex : '01-576-physicians').
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
        //
        if(count($params) == 3 && $params[2] == 'cache'){
            return self::cache();
        }
        //
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
        //
        $file = self::cacheFile();
        if(!is_file($file)){
            return "Missing file $file\nExecute first command: php run-g5.php gauq g55 gqid cache\n";
        }
        //
        switch($action){
        	case 'check': return self::check($groupKey); break;
        	case 'update': return "--- $cmdSignature $groupKey $action ---\n" . self::update($groupKey); break;
        }
    }
    
    // ******************************************************
    /** 
        Stores associations slug => GQID in a file.
        Must be executed before update() and check().
    **/
    private static function cache() {
        $res = '';
        $dblink = DB5::getDbLink();
        $query = "select slug,partial_ids from person where partial_ids->>'" . LERRCP::SOURCE_SLUG . "'::text != 'null'";
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $ids = json_decode($row['partial_ids'], true);
            $GQID = $ids[LERRCP::SOURCE_SLUG];
            $res .= $row['slug'] . ';' . $GQID . "\n";
        }
        $file = self::cacheFile();
        file_put_contents($file, $res);
        return "Associations slug - GQID stored in $file\n";
    }
    
    // ******************************************************
    /** 
        Computes the name of the file where cache is stored
    **/
    private static function cacheFile() {
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'slug-gqid.csv']);
    }
    
    // ******************************************************
    /** 
        Computes the name of the file where cache is stored
    **/
    private static function loadCache() {
        $tmp = file(self::cacheFile());
        $res = [];
        foreach($tmp as $line){
            $tmp2 = explode(';', trim($line));
            $res[$tmp2[0]] = $tmp2[1];
        }
        return $res;
    }
    
    // ******************************************************
    /** 
        The report is used to build (manually) G55::MATCH_LERRCP
    **/
    private static function check($groupKey) {
        $report = '';
        $dbSlugs = self::loadCache();
        $g55Slugs = self::computeSlugs($groupKey);
        
        return $report;
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
        Computes associations slug => NUM for a given g55 group.
        Auxiliary of update() and check().
    **/
    private static function computeSlugs($groupKey) {
        $res = []; // assoc $slug => $NUM
        $tmpfile = G55::tmpFilename($groupKey);
        if(!is_file($tmpfile)){
            die("UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n");
        }
        foreach(G55::loadTmpFile($groupKey) as $line){
            $slug = slugify::compute(substr($line['DATE'], 0, 10) . ' ' . $line['FNAME'] . ' ' . $line['GNAME']);
            $res[] = [$line['NUM'], $slug];
        }
        return $res;
    }
    
} // end class
