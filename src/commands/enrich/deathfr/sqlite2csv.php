<?php
/********************************************************************************
    Matches the death-fr sqlite database with g5 database to build 2 csv files in dat/tmp/enrich/death-fr:
    - death-fr-ok.csv       contains matches that can be included in g5 without human checks.
    - death-fr-check.csv    contains matches that need to be checked by a human.
    
    Once death-fr-check.csv is checked, these files can be copied in data/raw/enrich/death-fr to be versioned by the program.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-30 10:08:15+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use tiglib\patterns\Command;
use tiglib\strings\slugify;

class sqlite2csv implements Command {
    
    
    private static \PDO $sqlite;
    private static \PDO $db5;
    
    /** 
        @param $params  Array containing zero or one element.
                        One element: the slug of a g5 person to match to sqlite
                        Zero element: match all g5 persons
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // check params
        //
        $msg = "this command needs one parameter, indicating a date or a date range\n"
                . "Ex: php run-g5.php enrich deathfr sqlite2csv\n"
                . "    php run-g5.php enrich deathfr sqlite2csv bachelier-louis-1870-03-11\n";
        if(count($params) > 1){
            return "INVALID CALL: $msg";
        }
        $slug = count($params) == 1 ? $params[0] : '';
        
        $outfile_ok    = Deathfr::tmpDir() . DS . 'check' . DS . 'death-fr-ok.csv';
        $outfile_check = Deathfr::tmpDir() . DS . 'check' . DS . 'death-fr-check.csv';
        
        self::$sqlite = Deathfr::sqliteConnection();                                              
        self::$db5 = DB5::getDblink();
        
        $sqlite_stmt = self::$sqlite->prepare("select rowid,* from person where bday=:bday");
        $g5_persons = self::getG5Persons($slug);
        
        $res_ok = $res_check = "ID;V;FNAME;GNAME;BPLACE;BDAY;DDAY;DCODE\n";
        $N_ok = $N_check = 0;
        
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
                    //
                    // compare g5 / sqlite
                    //
                    $d1 = self::stringDistance($sqlite_person['fname'], $g5_person->data['name']['family']);
                    if($d1 > 2){
                        continue;
                    }
                    $d2 = self::stringDistance($sqlite_person['gname'], $g5_person->data['name']['given']);
                    if($d2 > 2){
                        continue;
                    }
                    $d3 = self::stringDistance($sqlite_person['bname'], $g5_person->data['birth']['place']['name']);
                    if($d3 > 2){
                        continue;
                    }
                    if(!self::matchBirthCountry($sqlite_person, $g5_person)){
                        continue;
                    }
                    if(!self::matchBirthC2($sqlite_person, $g5_person)){
                        continue;
                    }
                    //
                    // build result
                    //
                    $new = $g5_person->data['slug']
                    . ';' // valid
                    . ';' . $g5_person->data['name']['family']
                    . ';' . $g5_person->data['name']['given']
                    . ';' . $g5_person->data['birth']['place']['name']
                    . ';' . $bday
                    . ';' // dday
                    . ';' // dcode
                    . "\n";
                    $new .= $sqlite_person['rowid']
                    . ';' // valid
                    . ';' . $sqlite_person['fname']
                    . ';' . $sqlite_person['gname']
                    . ';' . $sqlite_person['bname']
                    . ';' . $bday
                    . ';' . $sqlite_person['dday']
                    . ';' . $sqlite_person['dcode']
                    . "\n";
                    $new .= ";;;;;;;\n";
                    // choose between ok / to check
                    if($d1 == 0 && $d2 == 0 && $d3 == 0){
                        $N_ok++;
                        $res_ok .= $new;
                    }
                    else{
                        $N_check++;
                        $res_check .= $new;
                    }
                } // end loop on sqlite person
            } // end loop on g5 person
        } // end loop on bday
        file_put_contents($outfile_ok, $res_ok);
        file_put_contents($outfile_check, $res_check);
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 4);
        echo "Execution took $dt s\n";
        echo "Generated $outfile_ok    --- $N_ok matches ok\n";
        echo "Generated $outfile_check --- $N_check matches to check\n";
    }
    
    /**
        @return Associative array of g5 persons grouped by birth day.
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
        //$query .= ' limit 200';
        
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
        
    /**
        Returns the minimal distance between 2 strings.
        The use of slugify permits to remove all accents and to have the computation case-insensitive.
        As the strings can be composed (ex: "Jean-Claude", or "Jean Claude"),
        the comparison is done between each components of the two strings.
    **/
    private static function stringDistance($str1, $str2): int {
        $str1 = slugify::compute($str1);
        $str2 = slugify::compute($str2);
        $min = 10;
        $tests = [];
        $parts1 = explode('-', $str1);
        $parts2 = explode('-', $str2);
        foreach($parts1 as $p1){
            foreach($parts2 as $p2){
                $l = levenshtein($p1, $p2);
                if($l < $min){
                    $min = $l;
                }
            }
        }
        return $min;
    }
    
    /**
        Returns a boolean indicating if the birth countries are identical.
    **/
    private static function matchBirthCountry(array &$sqlite, Person $g5): bool {
        $cy_sqlite = $sqlite['bcountry'];
        if($cy_sqlite == ''){
            $cy_sqlite = 'FR';
        }
        $cy_g5 = $g5->data['birth']['place']['cy'];
        if($cy_sqlite != $cy_g5){ // $cy_g5 is never empty
            return false; // in practice, false for persons born out of metropolitan France
        }
        return true;
    }
    
    /**
        Returns a boolean indicating if the birth c2 are identical.
        (c2 = admin code level 2 = département for France)
    **/
    private static function matchBirthC2(array &$sqlite, Person $g5): bool {
        $c2_g5 = $g5->data['birth']['place']['c2'];
        $c2_sqlite = substr($sqlite['bcode'], 0, 2);
        if($c2_sqlite != '' && $c2_g5 != '' && $c2_sqlite != $c2_g5){
            return false;
        }
        return true;
    }
    
}// end class
