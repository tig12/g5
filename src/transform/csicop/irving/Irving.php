<?php
/******************************************************************************
    File sent by Kenneth Irving
    
    @license    GPL
    @history    2019-12-23 00:38:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\irving;

use g5\Config;

class Irving{
    
    const RAW_CSV_SEP = ';';
    
    /**
        Field names of tmp_filename() for step raw2csv.
        Other fields complete this list in following transformations.
    **/
    const TMP_FIELDS = [
        'CSID',
        'FNAME',
        'GNAME',
        'DATE',
        'TZ',
        'C2',
        'LG',
        'LAT',
        'SPORT',
        'MA36',
        'CANVAS',
    ];
    
    /** Irving's raw file **/
    public static function raw_filename(){
        return Config::$data['dirs']['1-irving-raw'] . DS . 'rawlins-ertel-data.csv';
    }
    
    /** Generated file in 5-tmp **/
    public static function tmp_filename(){
        return Config::$data['dirs']['5-csicop'] . DS . '408-csicop-irving.csv';
    }
    
}// end class
