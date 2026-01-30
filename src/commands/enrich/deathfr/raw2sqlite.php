<?php
/********************************************************************************
    
    Fills the sqlite database with the contents of the files retrieved from data.gouv.fr.
    
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
                // here, should check:
                // - that $from < $to
                // - that $from >= min(available years)
                // - that $to >= max(available years)
                // - that all dates between $from and $to correspond to existing dates
                // not done because it's a build command, executed by a person supposed to be careful
                $years = range($from, $to);
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
        
        foreach($years as $y){
            self::processYear($y);
        }
    }
    
    private static function processYear(string $y): void {
        echo "======= Processing year $y =======\n";
        $N = 0;
        $t1 = microtime(true);
        $sqlite = Deathfr::sqliteConnection();
        $sqlite->beginTransaction();
        $file = 'compress.bzip2://' . Config::$data['dirs']['ROOT'] . DS . Deathfr::tmpDir() . DS . 'raw' . DS . "deces-$y.txt.bz2";
        try{
            foreach(yieldFile::loop($file) as $line){
                $fields = self::parseLine($line);
                self::$insert_stmt->execute($fields);
                $N++;
            }
        }
        catch(\Exception $e){
            $sqlite->rollback();
            throw $e;
        }
        $sqlite->commit();
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 4);
        echo "$N lines - $dt s\n";
    }
    
    // patterns to parse a line
    const string P_NAME = '#(.*?)\*(.*)/#';
    /** 
        See file README for line format and example.
    **/
    public static function parseLine(string $line): array {
        $res = [];
        $report = '';
        // name
        $tmp = trim(substr($line, 0, 80));
        preg_match(self::P_NAME, $tmp, $m);
        if(count($m) == 3) {
            $res['fname'] = $m[1];
            $res['gname'] = $m[2];
        }
        else {
            $report .= "Unable to parse name";
            $res['fname'] = '';
            $res['gname'] = '';
        }
        // sex
        $sex = substr($line, 80, 1);
        $res['sex'] = $sex == '1' ? 'M' : ($sex == '2' ? 'F' : '?');
        // birth date
        $tmp = substr($line, 81, 8);
        $res['bday'] = substr($tmp, 0, 4) . '-' . substr($tmp, 4, 2) . '-' . substr($tmp, 6);
        // birth place code
        $res['bcode'] = substr($line, 89, 5);
        // birth place name
        $res['bname'] = trim(substr($line, 94, 30));
        // birth country
        $res['bcountry'] = trim(substr($line, 124, 30));
        // death date
        $tmp = substr($line, 154, 8);
        $res['dday'] = substr($tmp, 0, 4) . '-' . substr($tmp, 4, 2) . '-' . substr($tmp, 6);
        // death code
        $res['dcode'] = substr($line, 162, 5);
        // death act number
        $res['dact'] = trim(substr($line, 167, 9));
        if($report != ''){
            echo "$line$report\n";
        }
        return $res;
    }
    
}// end class    
