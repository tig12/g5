<?php
/******************************************************************************
    File containing 408 sportsmen from CSICOP test.
    Sent by Kenneth Irving, originally coming from Dennis Rawlins
    
    @license    GPL
    @history    2019-12-23 00:38:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\Config;
use tiglib\arrays\csvAssociative;

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
        'LG',
        'LAT',
        'C2',
        'CY',
        'SPORT',
        'MA36',
        'CANVAS',
    ];
    
    /** 
        Modifications done on ids during raw2csv step.
        Purpose of these modifs is to have the same ids in SI42 and Irving.
        SI42 order was prefered to Irving because :
            - alphabetical order is respected
            - SI42 is the only known published group
        Format : Irving id => SI42 id.
        This correspondance is only used in raw2csv.
        After this step, Irving ids and SI42 ids are identical.
    **/
    const IRVING_SI42 = [
        180 => 181,
        181 => 180,
        211 => 210,
        210 => 211,
        266 => 267,
        267 => 268,
        268 => 269,
        269 => 270,
        270 => 271,
        271 => 266,
        354 => 355,
        355 => 354,
    ];
    
    /** Irving's raw file **/
    public static function raw_filename(){
        return Config::$data['dirs']['1-irving-raw'] . DS . 'rawlins-ertel-data.csv';
    }
    
    /** Generated file in 5-tmp **/
    public static function tmp_filename(){
        return Config::$data['dirs']['5-csicop'] . DS . '408-csicop-irving.csv';
    }
    
    
    // ******************************************************
    /**
        Loads 5-ciscop/408-csicop-irving.csv in an asssociative array ; keys = CSID
    **/
    public static function loadTmpCsv_csid(){
        $csv = csvAssociative::compute(self::tmp_filename(), G5::CSV_SEP);
        $res = [];              
        foreach($csv as $row){
            $res[$row['CSID']] = $row;
        }
        return $res;
    }
    
}// end class
