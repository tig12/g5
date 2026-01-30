<?php
/********************************************************************************
    
    Uses the sqlite database to match g5 data and add death date.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-30 10:08:15+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use tiglib\patterns\Command;

class sqlite2db implements Command {
    
    
    private static \PDO $sqlite;
    private static \PDO $db5;
    
    /** 
        @param $params  Array containing zero or one element.
                        One element: the slug of a g5 person to match to sqlite
                        Zero elements: match all g5 persons
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // check params
        //
        $msg = "this command needs one parameter, indicating a date or a date range\n"
                . "Ex: php run-g5.php enrich deathfr sqlite2db\n"
                . "    php run-g5.php enrich deathfr sqlite2db bachelier-louis-1870-03-11\n";
        if(count($params) > 1){
            return "INVALID CALL: $msg";
        }
        
        $slug = count($params) == 1 ? $params[0] : '';
        
        self::$sqlite = Deathfr::sqliteConnection();
        self::$db5 = DB5::getDblink();
        
        $sqlite_stmt = self::$sqlite->prepare("select * from person where bday=:bday");
        $g5_persons = self::getG5Persons($slug);
        foreach($g5_persons as $bday => $g5_persons){
            $sqlite_stmt->execute([':bday' => $bday]);
            $sqlite_persons = $sqlite_stmt->fetchAll(\PDO::FETCH_ASSOC);
            $n_sqlite = count($sqlite_persons);
echo "======== $bday ========\n";
echo "$n_sqlite sqlite persons\n";
            if($n_sqlite == 0) {
                continue;
            }
            foreach($g5_persons as $g5_person){
echo $g5_person->data['slug'] . "\n";
                foreach($sqlite_persons as $sqlite_person){
//echo '    ' . $sqlite_person['fname'] . ' ' . $sqlite_person['fname'] . "\n";
                } // end loop on sqlite person
            } // end loop on g5 person
        } // end loop on bday
    }
    
    /**
        Returns an associative array of g5 persons grouped by birth day.
        Keys = birth days
        Values = array of g5 persons born this day (objects of type Person).
        
    **/
    public static function getG5Persons(string $slug=''): array {
        $res = [];
        //$query = 'select id,slug,name,birth,death from person';
        $query = 'select slug from person';
        if($slug != ''){
            $query .= " where slug='$slug'";
        }
$query .= ' limit 5';
        
        $stmt = self::$db5->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $p = Person::createFromSlug($row['slug']);
            $bday = $p->birthday();
            if(!isset($res[$bday])){
                $res[$bday] = [];
            }
            $res[$bday][] = $p;
        }
        return $res;
    }
    
}// end class    
