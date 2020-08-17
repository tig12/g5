<?php
/******************************************************************************
    Arno Müller 402 italian writers
    Code common to muller402
    
    @license    GPL
    @history    2020-05-15 ~22h30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\Config;
use g5\model\DB5;
use g5\model\Source;
use g5\model\SourceI;


use tiglib\arrays\csvAssociative;
//use tiglib\strings\encode2utf8;

class Muller402 implements SourceI {
    
    const TRUST = DB5::TRUST_CHECK;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / edited
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'cura' . DS . '5muller_writers.yml';

    /** Separator used in the raw csv file **/
    const RAW_SEP = ';';
    
    /** Names of the columns of raw file 5muller_writers.csv **/
    const RAW_FIELDS = [
        'ID',
        'FNAME',
        'GNAME',
        'DATE',
        'TZ',
        'PLACE',
        'CY',
        'C2',
        'LG',
        'LAT ',
    ];
    
    // SourceI implementation
    public static function source(): Source{
        return Source::new(self::UID_SOURCE);
    }                                                                                         
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for 5muller_writers.xlsx. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['edited'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'newalchemypress.com', '05-muller-writers', '5muller_writers.csv']);
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
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers.csv']);
    }
    
    /**
        Loads file in a regular array
        @return Regular array ; each element contains an associative array (keys = field names).
    **/
    public static function loadOutputFile(){
        return csvAssociative::compute(self::tmp_csv_filename());
    }                                                                                              
    
}// end class
