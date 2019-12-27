<?php
/******************************************************************************

    List originally published in
    Skeptical Inquier VOL IV NO. 2 WINTER 1979-80, p 60 - 63.
                                   
    @license    GPL
    @history    2019-11-16 03:37:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\si42;

use g5\Config;

class SI42{
    
    /**
        Field names of tmp_filename() for step raw2csv.
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
    
    /** Raw file containing 408 records **/
    public static function raw_filename(){
        return Config::$data['dirs']['1-si-raw'] . DS . 'si42-p60-63.txt';
    }
    
    /** Raw file containing 128 records of canvas 1 **/
    public static function raw_filename_canvas1(){
        return Config::$data['dirs']['1-si-raw'] . DS . 'si42-p41.txt';
    }
    
    /** Tmp file name with 408 records **/
    public static function tmp_filename(){
        return Config::$data['dirs']['5-csicop'] . DS . '408-csicop-si42.csv';
    }
    
    /** Tmp file containing only records marked SC **/
    public static function tmp_filename_181(){
        return Config::$data['dirs']['5-csicop'] . DS . '181-csicop-si42.csv';
    }
    
}// end class
