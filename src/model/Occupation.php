<?php
/******************************************************************************
    Utilities for occupations.
    
    @license    GPL
    @history    2021-07-29 07:25:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\Config;
use tiglib\arrays\csvAssociative;

class Occupation {
    
    /**
        List of csv files containing the definitions of occupations.
        Relative to data/model/occu
    **/
    const DEFINITION_FILES = [
        'cura5.csv',
        'gauq-ertel-wd.csv',
        'general.csv',
    ];
    
    /** 
        Directory where the csv files containing lists by occupation are stored.
        Relative to data/output (see entry dirs / output in config.yml).
    **/
    const DOWNLOAD_BASEDIR = 'occupation';
    
    /** Stores the data of an Occupation object. **/
    public $data;
    
    // ***********************************************************************
    //                                  STATIC
    // ***********************************************************************
    
    /** 
        Returns the directory where sources are defined, in csv files.
    **/
    public static function getDefinitionDir(): string {
        return Config::$data['dirs']['model'] . DS . 'occu';
    }
    
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
        Returns an associative array slug => name for all occupations in database.
    **/
    public static function getAllSlugNames() {
        $dblink = DB5::getDbLink();
        $query = "select slug,name from groop where type='" . Group::TYPE_OCCU . "'";
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $res[$row['slug']] = $row['name'];
        }
        return $res;
    }
    
} // end class
