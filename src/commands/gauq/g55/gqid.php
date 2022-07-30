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
use tiglib\arrays\csvAssociative;

class gqid implements Command {
    
    const POSSIBLE_ACTIONS = [
        'check'     => 'Displays the possible matches between g55 and LERRCP',
        'update'    => 'Adds field GQID to tmp file',
    ];
    
    /** Separator used in cache file **/
    const CACHE_SEP = ';';
    
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
        $msg = "Usage : \n"
            . "php run-g5.php $cmdSignature cache\nComputes slug => GQID associations from db and stores it on disk\n"
            . "or:\n"
            . "php run-g5.php $cmdSignature <group> <action>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n"
            . "Possible values for <action>:\n";
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
            return "Missing file $file\nExecute first command: php run-g5.php $cmdSignature cache\n";
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
        $res = "SLUG;GQID;DATE;PLACE\n";
        $dblink = DB5::getDbLink();
        $query = "select slug,birth,partial_ids from person where partial_ids->>'" . LERRCP::SOURCE_SLUG . "'::text != 'null' order by slug";
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $ids = json_decode($row['partial_ids'], true);
            $birth = json_decode($row['birth'], true);
            $GQID = $ids[LERRCP::SOURCE_SLUG];
            $res .= implode(self::CACHE_SEP, [ $row['slug'], $GQID, ($birth['date'] != '' ? $birth['date'] : $birth['date-ut']), $birth['place']['name'] ]) . "\n";
        }
        $file = self::cacheFile();
        file_put_contents($file, $res);
        return "Generated $file\n";
    }
    
    // ******************************************************
    /** 
        Computes the name of the file where db gauq-cache associations are stored.
    **/
    private static function cacheFile() {
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'gauq-cache.csv']);
    }
    
    // ******************************************************
    /** 
        Loads file gauq-cache.csv in an associative array date => person data
    **/
    private static function loadCacheByDate() {
        $res = [];
        $tmp = csvAssociative::compute(self::cacheFile(), self::CACHE_SEP);
        foreach($tmp as $row){
            $day = substr($row['SLUG'], -10);
            if(!isset($res[$day])){
                $res[$day] = [];
            }
            $res[$day][] = $row;
        }
        return $res;
    }
    
    // ******************************************************
    /** 
        Computes associations slug => NUM for a given g55 group.
    **/
    private static function loadG55ByDate($groupKey) {
        $res = [];
        $tmpfile = G55::tmpFilename($groupKey);
        if(!is_file($tmpfile)){
            die("UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n");
        }
        foreach(G55::loadTmpFile($groupKey) as $row){
            $day = substr($row['DATE'], 0, 10);
            $slug = slugify::compute($row['FNAME'] . ' ' . $row['GNAME'] . ' ' . $day);
            $row['SLUG'] = $slug; // info not present in tmp file
            if(!isset($res[$day])){
                $res[$day] = [];
            }
            $res[$day][] = $row;
        }
        return $res;
    }
    
    // ******************************************************
    /** 
        The report is used to check that the proposed associations are correct,
        and fix the problematic cases with G55::MATCH_LERRCP
        Auxiliary function of check() and update()
    **/
    private static function check($groupKey) {
        $report = '';
        [$match, $nomatch] = self::match($groupKey);
        $report .= "=== MATCH ===\n";
        foreach($match as $element){
            $report .= "{$element['g55']['SLUG']} = g55 {$element['g55']['NUM']}\n";
            $report .= "{$element['lerrcp']['SLUG']} = lerrcp {$element['lerrcp']['GQID']}\n\n";
        }
        $report .= "=== NO MATCH ===\n";
        foreach($nomatch as $element){
            $report .= "{$element['NUM']} {$element['SLUG']}\n";
        }
        $report .= "=== " . count($match) . " MATCH ===\n"
           . "=== " . count($nomatch) . " NO MATCH ===\n";
        return $report;
    }
    
    // ******************************************************
    /** 
        Tries to match g55 to LERRCP.
        Strategy :
        - Looks if there is one match by birth day.
        - If one g55 birth day matches several LERRCP, a match is tried using slug.
        @return Array with 2 elements : 'match' and 'nomatch'.
                'match' and 'nomatch' are regular arrays
                Each element of 'match' contains 2 elements: the g55 row and the LERRCP row.
                Each element of 'nomatch' contains one element: the g55 row
    **/
    private static function match($groupKey) {
        $dbPersons = self::loadCacheByDate();
        $g55Persons = self::loadG55ByDate($groupKey);
        $match = [];
        $nomatch = [];
        foreach($g55Persons as $g55Day => $g55PersonsForThisDay){
            foreach($g55PersonsForThisDay as $g55Person){
//echo "\n<pre>"; print_r($g55Person); echo "</pre>\n"; exit;
                if(isset($dbPersons[$g55Day])){
                    if(count($dbPersons[$g55Day]) == 1){
                        // direct match, only one LERRCP with the g55 date
                        $match[] = [
                            'g55' => $g55Person,
                            'lerrcp' => $dbPersons[$g55Day][0],
                        ];
                    }
                    else {
                        // several possible matches, try to match by slug
                        $min_leven = PHP_INT_MAX;
                        $bestCandidate = null;
                        foreach($dbPersons[$g55Day] as $dbPerson){
                            $dbSlug = $dbPerson['SLUG'];
                            $g55Slug = $g55Person['SLUG']; 
                            $leven = levenshtein($dbSlug, $g55Slug);
                            if($leven < $min_leven){
                                $min_leven = $leven;
                                $bestCandidate = $dbPerson;
                            }
                        }
                        if($min_leven > 2){
                            $nomatch[] = $g55Person;
                        }
                        else {
                            $match[] = [
                                'g55' => $g55Person,
                                'lerrcp' => $bestCandidate,
                            ];
                        }
                    }
                }
                else {
                    // no LERRCP with g55 day
                    $nomatch[] = $g55Person;
                }
            }
        }
        return [$match, $nomatch];
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
    
} // end class
