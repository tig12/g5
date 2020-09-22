<?php
/******************************************************************************
    List of 100 italian writers without birth time published by Arno Müller
    in Astro-Forschungs-Daten 1
    
    @license    GPL
    @history    2020-08-18 19:13:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\Config;
use g5\model\Source;
use g5\model\SourceI;
use g5\model\Group;


use tiglib\arrays\csvAssociative;
//use tiglib\strings\encode2utf8;

class Muller100 implements SourceI {
    
    // TRUST_LEVEL not defined, using value of class Newalch
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / build
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'g5' . DS . 'muller-afd1-100-writers.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller100writers';

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

    // *********************** Source management ***********************
    
    /** Returns a Source object for 5muller_writers.xlsx. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['build'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller1083.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 100 Italian writers";
        $g->data['description'] = "100 Italian writers without birth time, gathered by Arno Müller";
        $g->data['id'] = $g->insert();
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file muller-afd1-100-writers.txt
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'newalchemypress.com', '05-muller-writers', 'muller-afd1-100-writers.txt']);
    }
    
    /** Loads csv file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename());
    }                                                                                              
    
    // *********************** Tmp files manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-100-it-writers.csv']);
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
        Returns the name of a "tmp raw file" : data/tmp/newalch/muller-100-it-writers-raw.csv
        (files used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-100-it-writers-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
}// end class
