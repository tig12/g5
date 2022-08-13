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
    
        @param  $params Array containing :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "gqid" (useless here, used by GauqCommand).
                        - For other params, see $msg below
    **/
    public static function execute($params=[]): string {
        
        $cmdSignature = 'gauq g55 gqid';
        
        $possibleParams = G55::getPossibleGroupKeys();
        $msg = "Usage : \n"
            . "1 - php run-g5.php $cmdSignature cache\n"
            . "  Computes LERRCP lines from db and stores it in file data/tmp/gauq/g5/gauq-cache.csv\n"
            . "  Needs to be done only once for all files."
            . "or:\n"
            . "2 - php run-g5.php $cmdSignature check <group> <what>\n"
            . "  Displays matching / not matching records between g55 and LERRCP\n"
            . "  <what> can be (case unsensitive):\n"
            . "  - M (display matching rows) \n"
            . "  - N (display not matching rows) \n"
            . "  - M+N or N+M (display both matching and not matching rows) \n"
            . "or:\n"
            . "3 - php run-g5.php $cmdSignature update <group>\n"
            . "  Fills column GQID of tmp file corresponding to <group>\n"
            . "Possible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        if(count($params) < 3){
            return "INVALID CALL - this command needs at least one other parameter.\n" . $msg;
        }
        //
        // ===== cache =====
        //
        if($params[2] == 'cache'){
            if(count($params) != 3){
                return "USELESS PARAMETER";
            }
            return self::cache();
        }
        //
        // ===== check =====
        //
        if($params[2] == 'check'){
            if(count($params) != 5){
                return "INVALID CALL: - this command needs exactly 2 parameters, <group> and <what>.\n$msg";
            }
            $groupKey = $params[3];
            if(!in_array($groupKey, $possibleParams)){
                return "INVALID PARAMETER <group>: $groupKey\n$msg";
            }
            $what = $params[4];
            $whatLow = strtolower($what);
            $possibles = ['m', 'n', 'm+n', 'n+m'];
            if(!in_array($whatLow, $possibles)){
                return "INVALID PARAMETER <what>: $what\n$msg";
            }
            return self::check($groupKey, $what);
        }
        //
        // ===== update =====
        //
        if($params[2] == 'update'){
            if(count($params) != 4){
                return "INVALID CALL: - this command needs exactly 1 parameter, <group>.\n$msg";
            }
            $groupKey = $params[3];
            if(!in_array($groupKey, $possibleParams)){
                return "INVALID PARAMETER <group>: $groupKey\n$msg";
            }
            return self::update($groupKey);
        }
        else{
            return "INVALID ACTION {$params[2]}\n$msg";
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
        $report = "--- gauq g55 cache ---\n";
        $report .= "Generated $file\n";
        return $report;
    }
    
    /** 
        Computes the name of the file where db gauq-cache associations are stored.
    **/
    private static function cacheFile() {
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'gauq-cache.csv']);
    }
    
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
    
    /** Finds a person in gauq-cache.csv' **/
    private static function findPersonFromCacheByGqid($GQID) {
        $tmp = csvAssociative::compute(self::cacheFile(), self::CACHE_SEP);
        foreach($tmp as $row){
            if($row['GQID'] == $GQID){
                return $row;
            }
        }
        throw new \Exception("Unable to findPersonFromCacheByGqid($GQID)");
    }
    
    // ******************************************************
    /** 
        Computes associations day => g55 records, for a given g55 group.
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
//echo "{$row['NUM']} $slug\n";
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
    private static function check($groupKey, $paramWhat) {
        $paramWhat = strtolower($paramWhat); // $paramWhat is supposed to be valid
        if($paramWhat == 'm'){
            $what = ['M'];
        }
        else if($paramWhat == 'n'){
            $what = ['N'];
        }
        else if($paramWhat == 'n+m' || $paramWhat == 'm+n'){
            $what = ['N', 'M'];
        }
        //
        $report = "--- gauq g55 gqid check $groupKey $paramWhat ---\n";
        [$match, $nomatch] = self::match($groupKey);
        //
        if(in_array('M', $what)){
            $report .= "=== MATCH ===\n";
            foreach($match as $element){
                $report .= "{$element['g55']['SLUG']} = g55 {$element['g55']['NUM']}\n";
                $report .= "{$element['lerrcp']['SLUG']} = lerrcp {$element['lerrcp']['GQID']}\n\n";
                // next line was used to generate G55::MATCH_LERRCP['10-884-priests']
                //$report .= "            '{$element['g55']['NUM']}'   => 'none',\n";
            }
        }
        if(in_array('N', $what)){
            $report .= "=== NO MATCH ===\n";
            foreach($nomatch as $element){
                $report .= "{$element['NUM']} {$element['SLUG']}\n";
            }
        }
        $report .= "=== " . count($match) . " MATCH ===\n=== " . count($nomatch) . " NO MATCH ===\n";
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
                $NUM = $g55Person['NUM'];
                // person handled in a previous run and stored in G55::MATCH_LERRCP
                if(isset(G55::MATCH_LERRCP[$groupKey][$NUM])){
                    if(G55::MATCH_LERRCP[$groupKey][$NUM] == 'none'){
                        $nomatch[] = $g55Person;
                    }
                    else{
                        $GQID = G55::MATCH_LERRCP[$groupKey][$NUM];
                        $match[] = [
                            'g55' => $g55Person,
                            'lerrcp' => self::findPersonFromCacheByGqid($GQID),
                        ];
                    }
                    continue;
                }
                // Person not in G55::MATCH_LERRCP
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
        if(!is_file($tmpfile)){
            return "UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n";
        }
        
        $report = "--- gauq g55 gqid update $groupKey\n";
        
        $N = 0;
        [$match, $nomatch] = self::match($groupKey);
        $res = implode(G5::CSV_SEP, G55::TMP_FIELDS) . "\n";
        foreach(G55::loadTmpFile($groupKey) as $line){
            $NUM = $line['NUM'];
            // find in $match the element with $NUM
            $found = false;
            foreach($match as $matchElement){
                if($matchElement['g55']['NUM'] == $NUM){
                    $found = true;
                    break;
                }
            }
            if($found){
                $line['GQID'] = $matchElement['lerrcp']['GQID'];
                $N++;
            }
            else{
                $line['GQID'] = '';
            }
            $res .= implode(G5::CSV_SEP, $line) . "\n";
        }
        file_put_contents($tmpfile, $res);
        $report .= "$N lines modified in $tmpfile\n";
        return $report;
    }
    
} // end class
