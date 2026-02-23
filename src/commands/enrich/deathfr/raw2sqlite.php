<?php
/********************************************************************************
    
    Fills the sqlite database with the contents of the files retrieved from data.gouv.fr
    
    php run-g5.php enrich deathfr raw2sqlite 1970-2025 > data/tmp/enrich/death-fr/sqlite-build-report-2026-02-22.log
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-28 22:36:21+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use tiglib\patterns\Command;
use tiglib\filesystem\yieldFile;

class raw2sqlite implements Command {
    
    const string QUERY_INSERT = <<<SQL
insert into person(
    fname,
    gname,
    sex,
    bday,
    bcode,
    bname,
    bcountry,
    dday,
    dcode,
    dact
)
values(
    :fname,
    :gname,
    :sex,
    :bday,
    :bcode,
    :bname,
    :bcountry,
    :dday,
    :dcode,
    :dact
)
SQL;
    
    private static \PDOStatement $insert_stmt;
    
    /** Total number of parsed lines **/
    private static int $n = 0;
    
    /** Total number of inserted lines **/
    private static int $nInserted = 0;
    
    const string ERR_NAME = 'ERR_NAME';
    const string ERR_BDAY = 'ERR_BDAY';
    const string ERR_DDAY = 'ERR_DDAY';
    const string ERR_POSTERIOR = 'ERR_POSTERIOR';
    const string ERR_EXCEPTION = 'ERR_EXCEPTION';
    
    private static array $nErrors = [
        self::ERR_NAME => 0,
        self::ERR_BDAY => 0, 
        self::ERR_DDAY =>  0,                          
        self::ERR_POSTERIOR => 0, 
        self::ERR_EXCEPTION =>  0,
    ];
    
    private static array $errDescr = [
        self::ERR_NAME => "incorrect name - inserted anyway",
        self::ERR_BDAY => "incorrect birth day - not inserted", 
        self::ERR_DDAY =>  "incorrect death day - not inserted",
        self::ERR_POSTERIOR => "birth posterior to death - not inserted", 
        self::ERR_EXCEPTION =>  "exception",
    ];
    
    /** 
        @param $params Array containing one element: a string indicating a date or a date range
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // check params
        //
        $msg = "this command needs one parameter, indicating a date or a date range\n"
                . "Ex: php run-g5.php enrich deathfr raw2sqlite 1970\n"
                . "    php run-g5.php enrich deathfr raw2sqlite 1970-1980\n";
        if(count($params) != 1){
            return "INVALID CALL: $msg";
        }
        
        $years = [];
        $p_year = '/^\d{4}$/';
        $p_range = '/^\d{4}-\d{4}$/';
        
        preg_match($p_year, $params[0], $m);
        if(count($m) == 1){
            $years[] = $m[0];
        }
        else {
            preg_match($p_range, $params[0], $m);
            if(count($m) == 1){
                $from = substr($m[0], 0, 4);
                $to = substr($m[0], 5);
                $years = range($from, $to); // if $to > $from, range() returns years from $to to $from
            }
            else {
                return "INVALID PARAMETER: {$params[0]}\n$msg";
            }
        }
        //
        // main loop
        //
        $sqlite = Deathfr::sqliteConnection();
        self::$insert_stmt = $sqlite->prepare(self::QUERY_INSERT);
        
        $t1 = microtime(true);
        foreach($years as $y){
            self::processYear($y);
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 2);
        
        echo "-------------------------------------------------------\n";
        echo "-------------------------------------------------------\n";
        echo "Total Execution time: $dt s\n";
        echo "-------------------------------------------------------\n";
        echo "-------------------------------------------------------\n";
        echo self::$n . " lines parsed\n";
        echo self::$nInserted . " lines inserted\n";
        echo "----------------------- ERRORS ------------------------\n";
        foreach(self::$nErrors as $errType => $nError){
            echo "$errType: $nError " . self::$errDescr[$errType] . "\n";
        }
        $nDateSkipped = self::$nErrors[self::ERR_BDAY] + self::$nErrors[self::ERR_DDAY] + self::$nErrors[self::ERR_POSTERIOR];
        echo "=> skipped $nDateSkipped lines because of date problem\n";
    }
    
    private static function processYear(string $y): void {
        echo "======= Processing year $y =======\n";
        $t1 = microtime(true);
        $sqlite = Deathfr::sqliteConnection();
        $sqlite->beginTransaction();
        $file = 'compress.bzip2://' . Config::$data['dirs']['ROOT'] . DS . Deathfr::tmpDir() . DS . 'raw' . DS . "deces-$y.txt.bz2";
        try{
            foreach(yieldFile::loop($file) as $line){
                self::$n++;
                [$fields, $ok] = self::parseLine($line);
                if($ok) {
                    self::$insert_stmt->execute($fields);
                    self::$nInserted++;
                }
            }
        }
        catch(\Exception $e){
            $sqlite->rollback();
            throw $e;
        }
        $sqlite->commit();
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 4);
        
        echo "======= End year $y - $dt s =======\n";
    }
    
    // patterns to parse a line
    const string P_NAME = '#(.*?)\*(.*)/#';
    /** 
        @return array with 2 elements : 
            - $fields:  associative array containing the parsed line
            - $ok:      boolean, true if the dates are valid and coherent.
        See file README for line format and example.
    **/
    public static function parseLine(string $line): array {
        try{
            $fields = [];
            // name
            $tmp = trim(substr($line, 0, 80));
            preg_match(self::P_NAME, $tmp, $m);
            if(count($m) == 3) {
                $fields['fname'] = $m[1];
                $fields['gname'] = $m[2];
            }
            else {
                self::report($line, self::ERR_NAME);
                self::$nErrors[self::ERR_NAME]++;
                $fields['fname'] = $tmp;
                $fields['gname'] = '';
            }
            // sex
            $sex = substr($line, 80, 1);
            $fields['sex'] = $sex == '1' ? 'M' : ($sex == '2' ? 'F' : '?');
            // birth date
            $tmp = substr($line, 81, 8);
            $y = substr($tmp, 0, 4);
            $m = substr($tmp, 4, 2);
            $d = substr($tmp, 6);
            if(checkdate($m, $d, $y) === false){
                self::$nErrors[self::ERR_BDAY]++;
                self::report($line, self::ERR_BDAY);
                return [[], false];
            }
            $fields['bday'] = "$y-$m-$d";
            // birth place code
            $fields['bcode'] = substr($line, 89, 5);
            // birth place name
            $fields['bname'] = trim(substr($line, 94, 30));
            // birth country
            $fields['bcountry'] = trim(substr($line, 124, 30));
            // death date
            $tmp = substr($line, 154, 8);
            $y = substr($tmp, 0, 4);
            $m = substr($tmp, 4, 2);
            $d = substr($tmp, 6);
            if(checkdate($m, $d, $y) === false){
                self::$nErrors[self::ERR_DDAY]++;
                self::report($line, self::ERR_DDAY);
                return [[], false];
            }
            $fields['dday'] = "$y-$m-$d";
            // birth / death days coherence
            if($fields['bday'] > $fields['dday']){
                self::$nErrors[self::ERR_POSTERIOR]++;
                self::report($line, self::ERR_POSTERIOR);
                return [[], false];
            }
            // death code
            $fields['dcode'] = substr($line, 162, 5);
            // death act number
            $fields['dact'] = trim(substr($line, 167, 9));
            return [$fields, true];
        }
        catch(\Throwable $e){
            self::$nErrors[self::ERR_EXCEPTION]++;
            self::report($line,
                         self::ERR_EXCEPTION,
                        "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString()
                );
            return [[], false];
        }
    }
    
    private static function report($line, $errCode, $msg='') {
        echo "$errCode $line";
        if($msg != '') echo "\n    $msg";
    }
    
}// end class
