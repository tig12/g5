<?php
/******************************************************************************
    
    Contains constants related to wikidata.

    @license    GPL
    @history    2019-05-16 12:58:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\wd;

use g5\Config;

class Wikidata{
    
    /** Separator used in raw wikidata files **/
    const RAW_CSV_SEP = ',';
    
    /** Base url to query WDQS **/
    const QUERY_URL = 'https://query.wikidata.org/sparql?format=json&query=';

//    const BASE_URL = 'http://www.wikidata.org/entity/';
    
//    const PATTERN_LG_LAT = '/Point\((.*?) (.*?)\)/';
    
    /** 
        List of profession codes used as departure points for wikidata retrieval.
    **/
    const OCCUPATION_SEEDS = [
        'Q2066131'  => 'athlete',
        'Q901'      => 'scientist',
        'Q39631'    => 'physician',
        'Q189290'   => 'military-officer',
        'Q483501'   => 'artist',
        'Q82955'    => 'politician',
        'Q482980'   => 'author',
    ];
    
    // ******************************************************
    /**
        Computes the directory where files containing profession lists are stored
    **/
    public static function getRawProfessionListDir(){
        return Config::$data['dirs']['1-wd-raw'] . DS . 'profession-lists';
    }
    
    // ******************************************************
    /**
        Computes the directory where files containing person lists are stored
        This directory contains 26 sub-directories (a ... z)
    **/
    public static function getRawPersonListBaseDir(){
        return Config::$data['dirs']['1-wd-raw'] . DS . 'person-lists';
    }
    
    // ******************************************************
    /**
        Queries WDQS
        @param  $query Query to issue, not url encoded.
        @return Array with 2 elements :
                - The http response code
                - The result of the query ; contains false if response code != 200
    **/
    public static function query($query){
        $url = self::QUERY_URL . urlencode($query);
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,  $url);
        curl_setopt( $ch, CURLOPT_POST, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch , CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/sparql-results+json"
        ]);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0');
        $result = curl_exec($ch);
        $respCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return [$respCode, $result];
    }
    
}// end class
