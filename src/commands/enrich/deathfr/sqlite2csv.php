<?php
/********************************************************************************
    
    Matches the sqlite database with g5 database to build a csv with matching candidates.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-30 10:08:15+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use tiglib\patterns\Command;

class sqlite2csv implements Command {
    
    
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
        
        $outfile = Deathfr::tmpDir() . DS . 'check' . DS . 'death-fr-check1.csv';
        
        self::$sqlite = Deathfr::sqliteConnection();                                              
        self::$db5 = DB5::getDblink();
        
        $sqlite_stmt = self::$sqlite->prepare("select rowid,* from person where bday=:bday");
        $g5_persons = self::getG5Persons($slug);
        
        $res = "ID;V;FNAME;GNAME;BDAY;BPLACE;DDAY;DCODE\n";
        
        $N = 0;
        $t1 = microtime(true);
        foreach($g5_persons as $bday => $g5_persons){
            $sqlite_stmt->execute([':bday' => $bday]);
            $sqlite_persons = $sqlite_stmt->fetchAll(\PDO::FETCH_ASSOC);
            $n_sqlite = count($sqlite_persons);
            if($n_sqlite == 0) {
                continue;
            }
            foreach($g5_persons as $g5_person){
                foreach($sqlite_persons as $sqlite_person){
                    if(!self::match($sqlite_person, $g5_person)){
                        continue;
                    }
                    $N++;
                    $res .= $g5_person->data['slug']
                    . ';' // valid
                    . ';' . $g5_person->data['name']['family']
                    . ';' . $g5_person->data['name']['given']
                    . ';' . $bday
                    . ';' . $g5_person->data['birth']['place']['name']
                    . ';' // dday
                    . ';' // dcode
                    . "\n";
                    $res .= $sqlite_person['rowid']
                    . ';' // valid
                    . ';' . $sqlite_person['fname']
                    . ';' . $sqlite_person['gname']
                    . ';' . $bday
                    . ';' . $sqlite_person['bname']
                    . ';' . $sqlite_person['dday']
                    . ';' . $sqlite_person['dcode']
                    . "\n";
                    $res .= ";;;\n";
                } // end loop on sqlite person
            } // end loop on g5 person
        } // end loop on bday
        file_put_contents($outfile, $res);
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 4);
        echo "Generated $outfile in $dt s\n";
        echo "$N matching candidates\n";
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
//$query .= ' limit 500';
        
        $stmt = self::$db5->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $p = Person::createFromSlug($row['slug']);
            unset($p->data['history']); // useless for the matching, to save up memory
            $bday = $p->birthday();
            if(!isset($res[$bday])){
                $res[$bday] = [];
            }
            $res[$bday][] = $p;
        }
        return $res;
    }
    
    private static function match(array &$sqlite, Person $g5): bool {
        $d1 = self::stringDistance($sqlite['fname'], $g5->getFamilyName());
        if($d1 > 2){
            return false;
        }
        $d2 = self::stringDistance($sqlite['gname'], $g5->getGivenName());
        if($d2 > 2){
            return false;
        }
        if(!self::matchBirthplace($sqlite, $g5)){
            return false;
        }
//echo $sqlite['fname'] . ' / ' . $sqlite['gname'] . ' ----- ' . $g5->getFamilyName() . ' / ' . $g5->getGivenName() . "\n";
//echo "d1 = $d1, d2 = $d2\n";
        return true;
    }
    
    /**
        Returns a boolean indicating that the birth places are not incompatible.
        Does not compare the place names, only country and c2.
    **/
    private static function matchBirthplace(array &$sqlite, Person $g5): bool {
        $cy_sqlite = $sqlite['bcountry'];
        if($cy_sqlite == ''){
            $cy_sqlite = 'FR';
        }
        $cy_g5 = $g5->data['birth']['place']['cy'];
        if($cy_sqlite != $cy_g5){ // $cy_g5 is never empty
            return false; // in practice, false for persons born out of metropolitan France
        }
        // c2
        $c2_g5 = $g5->data['birth']['place']['c2'];
        $c2_sqlite = substr($sqlite['bcode'], 0, 2);
        if($c2_sqlite != '' && $c2_g5 != '' && $c2_sqlite != $c2_g5){
            return false;
        }
        return true;
    }

    /**
        Returns the minimal distance between 2 strings.
        As the strings can be composed (ex: "Jean-Claude", or "Jean Claude"),
        the comparison is done between each components of the two strings.
    **/
    private static function stringDistance($str1, $str2): int {
        $min = 10;
        $tests = [];
        $parts1 = preg_split('/\W/', strtolower($str1));
        $parts2 = preg_split('/\W/', strtolower($str2));
        foreach($parts1 as $p1){
            foreach($parts2 as $p2){
                $l = levenshtein($p1, $p2);
                if($l < $min){
                    $min = $l;
                }
            }
        }
// echo "===================\n$str1\n";
// echo "$str2\n";
// echo "\n"; print_r($parts1); echo "\n";    
// echo "\n"; print_r($parts2); echo "\n";
        return $min;
    }
    
}// end class    
