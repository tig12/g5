<?php
/******************************************************************************
    Utilities for occupations.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-29 07:25:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\app\Config;
use tiglib\arrays\csvAssociative;

class Occupation {
    
    /**
        List of csv files containing the definitions of occupations.
        Relative to data/db/occu
    **/
    const DEFINITION_FILES = [
        'all-occus.csv',
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
        return Config::$data['dirs']['db'] . DS . 'occu';
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
