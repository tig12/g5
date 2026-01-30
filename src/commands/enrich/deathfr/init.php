<?php
/********************************************************************************
    
    Initializes the local sqlite database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-28 22:36:11+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use tiglib\patterns\Command;

class init implements Command {
    
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        $path_sqlite = Deathfr::sqlitePath();
        if(is_file($path_sqlite)) {
            $answer = readline("WARNING: File $path_sqlite already exists.\n         This operation will delete it permanently. Are you sure (y/n)? ");
            if(strtolower($answer) != 'y') {
                if(strtolower($answer) != 'n') {
                    echo "WRONG ANSWER - respond with 'y' or 'n'. Nothing was modified\n";
                }
                else {
                    echo "OK, nothing was modified\n";
                }
                return;
            }
            unlink($path_sqlite);
            echo "Deleted file $path_sqlite\n";
        }
        
        $dir = dirname($path_sqlite);
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
            echo "Created directory $dir\n";
        }
        
        $sqlite = new \PDO('sqlite:' . $path_sqlite);
        $sql1 = <<<SQL
create table person(
    fname varchar(80),
    gname varchar(80),
    sex character(1),
    bday character(8),
    bcode character(5),
    bname character(30),
    bcountry varchar(80),
    dday character(8),
    dcode character(5),
    dact character(9)
)
SQL;
        $sql2 = 'create index idx_bday ON person(bday)';
        $sqlite->exec($sql1);
        $sqlite->exec($sql2);
        echo "Initialized local sqlite database $path_sqlite\n";
    }
    
}// end class    
