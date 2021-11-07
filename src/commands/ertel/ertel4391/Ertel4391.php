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
        Relative to directory data/db
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'ertel' . DS . '3a_sports.yml';
    
    /**
        Returns a Source object for the raw file used for Ertel4391.
    **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::RAW_SOURCE_DEFINITION);
    }

    // *********************** Group management ***********************
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'ertel4384sportsmen';
    
    /** Returns a Group object for Ertel4391. **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Ertel 4384 athletes";
        $g->data['description'] = "4384 athletes compiled by Suitbert Ertel\n(Ertel says 4391)";
        $g->data['id'] = $g->insert();
        return $g;
    }
    
    /** Returns a Group object for one of Ertel4391 subgroups. **/
    public static function getSubgroup($slug): Group {
        $g = new Group();
        $g->data['slug'] = $slug;
        $g->data['name'] = self::SUBGROUPS[$slug]['name'];
        $g->data['description'] = self::SUBGROUPS[$slug]['description'];
        $g->data['id'] = $g->insert();
        return $g;
    }
    
    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function rawFilename(){
        return Ertel::rawDirname() . DS . '3a_sports-utf8.txt';
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return Ertel::tmpDirname() . DS . 'ertel-4384-athletes.csv';
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
        return Ertel::tmpDirname() . DS . 'ertel-4384-athletes-raw.csv';
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
    // *********************** Tweak file manipulation ***********************
    public static function tweakFilename(){
        return Config::$data['dirs']['init'] . DS . 'newalch-tweak' . DS . '4391SPO.yml';
    }
    
    
} // end class
