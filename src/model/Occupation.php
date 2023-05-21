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
    /* 
    // removed because currently only one definition file is used
    const DEFINITION_FILES = [
        'all-occus.csv',
    ];
    */
    
    /**
        CSV file containing the definitions of occupations.
        Relative to data/db/occu
    **/
    const DEFINITION_FILE = 'all-occus.csv';
    
    
    /** 
        Directory where the csv files containing lists by occupation are stored.
        Relative to data/output (see entry dirs / output in config.yml).
    **/
    const DOWNLOAD_BASEDIR = 'occupation';
    
    /** Stores the data of an Occupation object. **/
    public $data;
    
    /** 
        Link to the database. Stored in a static variable to cache.
        Useful for commands/db/init/occus1, to avoid successive calls to create
        a new dblink for each call to insert()
    **/
    private static  $dblink = null;
    
    private static $stmt_insert = null;
    
    // ***********************************************************************
    //                                  STATIC
    // ***********************************************************************
    
    /** 
        Returns the directory where occupations are defined, in csv files.
    **/
    public static function getDefinitionDir(): string {
        return Config::$data['dirs']['db'] . DS . 'occu';
    }
    
    /** 
        Returns the path to data/db/occu/all-occus.csv
    **/
    public static function getDefinitionFile(): string {
        return self::getDefinitionDir() . DS . self::DEFINITION_FILE;
    }
    
    private static function getDBLink(){
        if(self::$dblink == null){
            self::$dblink = DB5::getDbLink();
        }
        return self::$dblink;
    }
    
    
    /**
        Returns an associative array slug => name for all occupations in database.
    **/
    public static function getAllSlugNames() {
        $query = "select slug,name from groop where type='" . Group::TYPE_OCCU . "'";
        $res = [];
        foreach(self::getDBLink()->query($query, \PDO::FETCH_ASSOC) as $row){
            $res[$row['slug']] = $row['name'];
        }
        return $res;
    }
    
    
    // ********************************* insert **************************************
    
    private static function getStmt_insert(){
        if(self::$stmt_insert == null){
            self::$stmt_insert = self::getDBLink()->prepare(
                "insert into groop(
                    slug,
                    wd,
                    name,
                    description,
                    type,
                    parents
                )values(?,?,?,?,?,?)"
            );
        }
        return self::$stmt_insert;
    }
    
    public static function insert($line){
        $parents = [];
        // obliged to add this test to prevent a bug:
        // if field parents is empty, explode returns an array containing one empty string
        if($line['parents'] != ''){
            $parents = explode('+', $line['parents']);
        }
        self::getStmt_insert()->execute([
            $line['slug'],
            $line['wd'],
            $line['en'],
            'Persons whose occupation is ' . $line['en'] . '.',
            Group::TYPE_OCCU,
            json_encode($parents),
        ]);
    }
    
} // end class
