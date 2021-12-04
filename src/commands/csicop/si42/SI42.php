<?php
/******************************************************************************

    List originally published in
    Skeptical Inquier VOL IV NO. 2 WINTER 1979-80, p 60 - 63.
                                   
    @license    GPL
    @history    2019-11-16 03:37:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use g5\app\Config;

class SI42 {
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'si42';
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'csicop' . DS . self::SOURCE_SLUG . '.yml';
    
    /**
        Field names of tmpFilename() for step raw2csv.
        Other fields complete this list in following transformations.
    **/
    const TMP_FIELDS = [
        'CSID',
        'FNAME',
        'GNAME',
        'DATE',                                                                                                          
        'C2',
        'MA12', // mars, 12 sectors
        'SC',   // selected champion, 181 records
    ];
    
    // *********************** Raw files manipulation ***********************
    
    /** Raw file containing 408 records **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'csicop', self::SOURCE_SLUG, 'si42-p60-63.txt']);
    }
    
    /** Raw file containing 128 records of canvas 1 **/
    public static function rawFilename_canvas1(){
        return implode(DS, [Config::$data['dirs']['raw'], 'csicop', self::SOURCE_SLUG, 'si42-p41.txt']);
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Tmp file name with 408 records **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'csicop', self::SOURCE_SLUG, 'csicop-408-si42.csv']);
    }
    
    /** Tmp file containing only records marked SC **/
    public static function tmpFilename_181(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'csicop', self::SOURCE_SLUG, 'csicop-181-si42.csv']);
    }
    
}// end class
