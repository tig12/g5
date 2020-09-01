<?php
/******************************************************************************
    Arno Müller's 402 italian writers
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

class Muller402 implements SourceI {
    
    const TRUST = DB5::TRUST_CHECK;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / db
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '5muller_writers.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller402writers';

    /** Separator used in the raw csv file **/
    const RAW_SEP = ';';
    
    /** Names of the columns of raw file 5muller_writers.csv **/
    const RAW_FIELDS = [
        'NAME',
        'YEAR',
        'MONTH',
        'DAY',
        'HOUR',
        'MIN',
        'TZO',
        'PLACE',
        'LAT',
        'LG',
    ];
    
    /** Names of the columns of raw file data/tmp/newalch/muller-402-it-writers.csv **/
    const TMP_FIELDS = [
        'MUID',
        'GQID',
        'FNAME',
        'GNAME',
        'SEX',
        'DATE',
        'TZO',
        'LMT',
        'PLACE',
        'CY',
        'C2',
        'LG',
        'LAT',
        'OCCU',
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
        return implode(DS, [Config::$data['dirs']['raw'], 'newalchemypress.com', '05-muller-writers', '5muller_writers.csv']);
    }
    
    /** Loads csv file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
                                                                                         
    // *********************** Tmp files manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the tmp file in an asssociative array.
            keys = Müller ids (MUID)
            values = assoc array containing the fields
    **/
    public static function loadTmpFile_id(){
        $rows1 = csvAssociative::compute(self::tmpFilename());
        $res = [];
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }                                                                                              
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", data/tmp/newalch/muller-402-it-writers-raw.csv
        (files used to keep trace of the original raw values).
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }

}// end class
