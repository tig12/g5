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


use tiglib\arrays\csvAssociative;
//use tiglib\strings\encode2utf8;

class Muller100 implements SourceI {
    
    const TRUST = DB5::TRUST_CHECK;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / db
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . 'muller-100-it-writers.yml';

    const TMP_FIELDS = [
            'MUID',
            'FNAME',
            'GNAME',
            'SEX',
            'DATE',
            'TZ',
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
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'newalchemypress.com', '05-muller-writers', 'muller-100-it-writers.txt']);
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
    
}// end class
