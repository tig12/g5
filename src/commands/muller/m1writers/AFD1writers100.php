<?php
/******************************************************************************
    List of 100 italian writers without birth time published by Arno Müller
    in Astro-Forschungs-Daten 1
    
    @license    GPL
    @history    2020-08-18 19:13:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m1writers;

use g5\app\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;

class AFD1writers100 {
    
    // TRUST_LEVEL not defined, using value of class Newalch
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/muller/1-writers/muller1-100-writers.txt
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd1-writers-list-100.yml';

    /** Slug of source muller1-100-writers.txt **/
    const LIST_SOURCE_SLUG = 'afd1-100';
    
    // constants BOOKLET_SOURCE_DEFINITION_FILE and BOOKLET_SOURCE_SLUG not defined here
    // (because they have the same values as AFD1writers)

    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller-afd1-100writers';

    const RAW_FIELDS = [
            'MUID',
            'FNAME',
            'GNAME',
            'SEX',
            'DATE',
            'TZO',
            'PLACE',
            'C2',
            'LG',
            'LAT',
            'OCCU',
            'OPUS',
            'LEN',
        ];

    const TMP_FIELDS = [
            'MUID',
            'FNAME',
            'GNAME',
            'SEX',
            'DATE',
            'TZO',
            'LMT',
            'PLACE',
            'C2',
            'CY',
            'LG',
            'LAT',
            'OCCU',
            'OPUS',
            'LEN',
        ];

        
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for muller100writers.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 100 Italian writers";
        $g->data['description'] = "100 Italian writers without birth time, gathered by Arno Müller";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['sources'] = [self::LIST_SOURCE_SLUG, AFD1writers::BOOKLET_SOURCE_SLUG];
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file muller1-100-writers.txt
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', '1-writers', 'muller1-100-writers.txt']);
    }
    
    /** Loads csv file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename());
    }                                                                                              
    
    // *********************** Tmp files manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/muller/1-writers/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '1-writers', 'muller1-100-writers.csv']);
    }
    
    /**
        Loads file in a regular array
        @return Regular array ; each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file" : data/tmp/muller/1-writers/muller1-100-writers-raw.csv
        (files used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '1-writers', 'muller1-100-writers-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
}// end class
