<?php
/******************************************************************************
    Code common to ertel4391
    
    @license    GPL
    @history    2019-05-11 23:15:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\ertel\ertel4391;

use g5\app\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;
use g5\commands\ertel\Ertel;

class Ertel4391 {
    
    /**
        Trust level for data coming from Ertel file
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    // *********************** Source management ***********************
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 3a_sports.txt.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'ertel' . DS . '3a_sports.yml';
    
    /** Slug of source 3a_sports.txt **/
    const SOURCE_SLUG = '3a_sports';
    
    // *********************** Group management ***********************
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'ertel-4384-sportsmen';
    
    /** Slugs of ertel4384sportsmen subgroups **/
    const SUBGROUP_SLUGS = [
        'ertel-1-first-french',
        'ertel-2-first-european',
        'ertel-3-italian-football',
        'ertel-4-german-various',
        'ertel-5-french-occasionals',
        'ertel-6-para-champions',
        'ertel-7-para-lowers',
        'ertel-8-csicop-us',
        'ertel-9-second-european',
        'ertel-10-italian-cyclists',
        'ertel-11-lower-french',
        'ertel-12-gauq-us',
        'ertel-13-plus-special',
    ];
    
    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalchemypress.com
    **/
    public static function rawFilename(){
        return Ertel::rawDirname() . DS . '3a_sports-utf8.txt';
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return Ertel::tmpDirname() . DS . 'ertel-4384-sportsmen.csv';
    }
    
    /**
        Loads the temporary file in a regular array
        Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the file temporary file in an asssociative array ; keys = NR
    **/
    public static function loadTmpFile_nr(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['NR']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /** Path to the temporary csv file keeping an exact copy of the raw file. **/
    public static function tmpRawFilename(){
        return Ertel::tmpDirname() . DS . 'ertel-4384-sportsmen-raw.csv';
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
    // *********************** Tweak file manipulation ***********************
    public static function tweakFilename(){
        return Config::$data['dirs']['init'] . DS . 'newalch-tweak' . DS . 'ertel-4384-sportsmen.yml';
    }
    
    
} // end class
