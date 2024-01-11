<?php 
/********************************************************************************
    Utilities to build search functionality.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2023-05-08 21:18:39+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace g5\model;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use tiglib\strings\slugify;

class Search {
    
    /**
        Computes the different forms of a person name.
        Names used to search a person.
        Family name always before given name.
        @param  $arrNames  Array representing the name, as stored in database. Ex: [
            'given' => Pierre
            'family' => Alard
            'nobl' => ''
            'spouse' => [],
            'official' => [
                'given' => ''
                'family' => ''
            ]
            'fame' => [
                'full' => ''
                'given' => ''
                'family' => ''
            ]
            'alter' => []
        ]
        @return A regular array of possible names
    **/
    public static function computePersonNames(array $arrNames) {
        $res = [];
        // "normal" name
        $fam = $arrNames['family'];
        if($fam != ''){ // normally it's always the case
            // ex Simone de Beauvoir
            // beauvoir-simone
            if($arrNames['given'] != ''){
                $res[] = $fam . ' ' . $arrNames['given'];
            }
            else{
                $res[] = $fam;
            }
            // de-beauvoir-simone
            if($arrNames['nobl'] != ''){
                if($arrNames['given'] != ''){
                    $res[] = $arrNames['nobl'] . ' ' . $fam . ' ' . $arrNames['given'];
                }
                else{
                    $res[] = $arrNames['nobl'] . ' ' . $fam;
                }
            }
        }
        // spouse
        foreach($arrNames['spouse'] as $spouse){
            if($arrNames['given'] != ''){
                $res[] = $spouse . ' ' . $arrNames['given'];
            }
            else{
                $res[] = $spouse;
            }
        }
        // fame 
        if($arrNames['fame']['full'] != ''){
            $res[] = $arrNames['fame']['full'];
        }
        if($arrNames['fame']['family'] != '' && $arrNames['fame']['given'] != ''){
            $res[] = $arrNames['fame']['family'] . ' ' . $arrNames['fame']['given'];
        }
        // maybe do not keep alternative names
        if(!empty($arrNames['alter'])){
            foreach($arrNames['alter'] as $alt){
                $res[] = $alt;
            }
        }
        return $res;
    }
    
    /**
        Adds a new person in table search.
    **/
    public static function addPerson(Person $p) {
        $dblink = DB5::getDbLink();
        $stmt_insert = $dblink->prepare("insert into search(search_term,slug,day,name)values(?,?,?,?)");
        $json_name = json_decode($p->data['name'], true);
        $slug = $p->data['slug'];
        $search_names = Search::computePersonNames($json_name);
        $name = $p->getCommonName(); ///////////////// TODO Implement
        $bday = substr($slug, -10);
        foreach($search_names as $search_name){
            $name_slug = slugify::compute($search_name);
            $stmt_insert->execute([$name_slug, $slug, $bday, $name]);
            $N_inserted++;
        }
    }
    
    /** 
        Recomputes completely table search used by ajax, for all persons stored in database.
        @param  $params empty array
        @return report.
    **/
    public static function addAllPersons(): string {
        $report = '';
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from search");
        $stmt_insert = $dblink->prepare("insert into search(search_term,slug,day,name)values(?,?,?,?)");
        $N_person = 0;
        $N_inserted = 0;
        $t1 = microtime(true);
        $query = "select slug,name from person";
        $stmt = $dblink->query($query);
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $slug = $row['slug'];
            $json_name = json_decode($row['name'], true);
            $search_names = Search::computePersonNames($json_name);
            $bday = substr($slug, -10);
            foreach($search_names as $search_name){
                $name_slug = slugify::compute($search_name);
                $stmt_insert->execute([$name_slug, $slug, $bday, $search_name]);
                $N_inserted++;
            }
            $N_person++;
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $report .= "Inserted $N_inserted lines for $N_person persons ($dt s)\n";
        return $report;
    }
    
} // end class
