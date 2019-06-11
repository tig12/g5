<?php
/******************************************************************************
    
    Contains constants related to wikidata.

    @license    GPL
    @history    2019-05-16 12:58:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\wd;

class Wikidata{
    
    /** Separator used in raw wikidata files **/
    const RAW_CSV_SEP = ',';
    
    const BASE_URL = 'http://www.wikidata.org/entity/';
    
    const PATTERN_LG_LAT = '/Point\((.*?) (.*?)\)/';

    // ******************************************************
    /**
        Computes a wikidata id from a url by removing the BASE_URL part.
    **/
    public static function getId($field){
        return str_replace(self::BASE_URL, '', $field);
    }
    
    
    // ******************************************************
    /**
        Parses a string containing latitude and logitude, as returned by wikidata.
        @param $coords a string like "Point(5.85528 59.3461)" (lg first ; lat second)
        @return Array with two elements : longitude and latitude (in this order).
                If can't be computed, returns an array with two empty strings.
    **/
    public static function parseLgLat($coords){
        preg_match(self::PATTERN_LG_LAT, $coords, $m);
        if(count($m) == 3){
            return [$m[1], $m[2]];
        }
        return ['', ''];
    }
    
}// end class