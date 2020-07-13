<?php
/******************************************************************************
    Code common to muller402
    Arno Müller 402 italian writers
    
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

class Muller402 implements SourceI{
    
    /** id in g5 db when this class represents a source **/
    const ID_SOURCE = 'muller402';
    
    /** uid in g5 db when this class represents a source **/
    const UID_SOURCE = 'source' . DB5::SEP . 'web' . DB5::SEP . 'newalch' .  DB5::SEP . 'muller402';
    //const UID_SOURCE = implode( DB5::SEP, ['source', 'web', 'newalch', 'muller402']);
    
    /** 
        Path to raw file, relative to data/1-raw from config.yml
        data/1-raw/newalchemypress.com/05-muller-writers/5muller_writers.csv
    **/
    const RAW_PATH = 'newalchemypress.com' . DS . '05-muller-writers' . DS . '5muller_writers.csv';
    
    /** Separator used in the raw csv file **/
    const RAW_SEP = ';';
    
    /** Name of the csv file in 9-output/ **/
    const OUT_CSV_FILE = 'muller402WRI.csv';
    
    /** Columns of file in 9-output/ **/
    const OUTPUT_COLUMNS = [
    ];
    
    // SourceI implementation
    public static function source(): Source{
        return Source::new(self::UID_SOURCE);
    }
    
    // ******************************************************
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function rawFilename(){
        return Config::$data['dirs']['1-raw'] . DS . self::RAW_PATH;
    }
    
    /**
        Loads csv file in a regular array
        @return Regular array
                Each element contains a regular array
    **/
    public static function loadRawFile(){
        return file(self::rawFilename());
    }                                                                                              
    
    /**
        @return Path to the csv file stored in 5-newalch-csv
    **/
    public static function outputFilename(){
        return Config::$data['dirs']['5-newalch-csv'] . DS . self::TMP_CSV_FILE;
    }
    
    /**
        Loads file in a regular array
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadOutputFile(){
        return csvAssociative::compute(self::tmp_csv_filename());
    }                                                                                              
    
}// end class
