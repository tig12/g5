<?php
/******************************************************************************
    Utilities for occupations.
    
    @license    GPL
    @history    2021-07-29 07:25:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\Config;
use g5\patterns\DAGStringNode;
use tiglib\arrays\csvAssociative;

class Occupation {
    
    /**
        List of csv files containing the definitions of occupations.
    **/
    const DEFINITION_FILES = [
        'cura5.csv',
        'gauq-ertel-wd.csv',
        'general.csv',
    ];
    
    /** Stores the data of an Occupation object. **/
    public $data;
    
    /**
        Associative array: occupation slug => array of slugs of ancestors.
        Computed by getAllAncestors().
    **/
    private static $allAncestors = null;
    
    /** 
        Returns the directory where sources are defined, in csv files.
    **/
    public static function getDefinitionDir(): string {
        return Config::$data['dirs']['model'] . DS . 'occu';
    }
    
    // ******************************************************
    /**
        Returns an associative array with
            keys = occupation codes for Ertel or Cura5
            values = array of slugs of corresponding occupations
                    (for most cases, this array has 1 element)
        @param  $what "cura5" or "ertel"
    **/
    public static function loadForMatch(string $what) {
        if(!in_array($what, ['cura5', 'ertel'])){
            throw new \Exception('Invalid value for parameter $what');
        }
        $res = [];
        foreach(Occupation::DEFINITION_FILES as $file){
            $lines = csvAssociative::compute(Occupation::getDefinitionDir() . DS . $file);
            foreach($lines as $line){
                if(!isset($line[$what])){
                    // for ex in general.csv, informations about cura5 and ertel
                    // are not present, so useless for match
                    break;
                }
                $code = $line[$what]; // in $what vocabulary - ex AVI or AIRP
                if($code == ''){
                    continue;
                }
                $slugs = explode('+', $line['slug']);
                $res[$code] = $slugs;
            }
        }
        return $res;
    }
    
    /**
        Computes self::$allAncestors
    **/
    public static function getAllAncestors() {
        if(self::$allAncestors != null){
            return self::$allAncestors;
        }
        $occusFromDB = self::loadAllFromDB();
        // 1 - $nodes = assoc array slug - DAGStringNode
        //     $occus = assoc array slug - Occupation object
        $nodes = [];
        $occus = [];
        foreach($occusFromDB as $occu){
            $slug = $occu->data['slug'];
            $nodes[$slug] = new DAGStringNode($slug);
            $occus[$slug] = $occu;
        }
        // 2 - add edges from parents
        foreach($occus as $occu){
            $slug = $occu->data['slug'];
            foreach($occu->data['parents'] as $parent){ // $parent is a slug
                if(!isset($nodes[$parent])){
                    $msg = "INCORRECT OCCUPATION DEFINITION - occupation = '$slug' ; parent = '$parent'";
                    throw new \Exception($msg);
                }
                $nodes[$slug]->addEdge($nodes[$parent]);
            }
        }
        // 3 - result
        self::$allAncestors = [];
        foreach($nodes as $slug => $node){
            self::$allAncestors[$slug] = $node->getReachableAsStrings();
        }
        return self::$allAncestors;
    }
    
    /**
        Returns an associative array slug => name for all occupations in database.
    **/
    public static function getAllSlugNames() {
        $dblink = DB5::getDbLink();
        $query = "select slug,name from occu";
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $res[$row['slug']] = $row['name'];
        }
        return $res;
    }
    
    /**
        Returns an array of Occupation objects, retrieved from database.
    **/
    public static function loadAllFromDB() {
        $dblink = DB5::getDbLink();
        $query = "select * from occu";
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $row['parents'] = json_decode($row['parents'], true);
            $tmp = new Occupation();
            $tmp->data = $row;
            $res[] = $tmp;
        }
        return $res;
    }
    
} // end class
