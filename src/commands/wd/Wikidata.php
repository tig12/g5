<?php
/******************************************************************************
    
    Contains constants related to wikidata.

    @license    GPL
    @history    2019-05-16 12:58:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wd;

use g5\app\Config;

class Wikidata{
    
    /**
        Path to the yaml file containing the characteristics of Newalchemypress source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'wikidata.yml';
    
    /**
        Slug of Wikidata information source.
    **/
    const SOURCE_SLUG = 'wd';
    
// ----------------------------------------------------------------------------
// Following code is experimental and currently not used to build the database.
// ----------------------------------------------------------------------------
    
    /** Separator used in raw wikidata files **/
    const RAW_CSV_SEP = ',';
    
    /** Base url for wikidata entities **/
    const ENTITY_URL = 'http://www.wikidata.org/entity';
    
    /** Base url to query WDQS **/
    const QUERY_URL = 'https://query.wikidata.org/sparql?format=json&query=';
    
    /** Wikidata id, like Q465841 **/
    const PATTERN_ID = '/Q\d+/';

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
    **/
    public static function getRawPersonListBaseDir(){
        return Config::$data['dirs']['1-wd-raw'] . DS . 'person-lists';
    }
    
    // ******************************************************
    /**
        Computes the directory where files containing full person data are stored
    **/
    public static function getRawPersonBaseDir(){
        return Config::$data['dirs']['1-wd-raw'] . DS . 'persons';
    }
    
    // ******************************************************
    /**
        Computes the directory where a raw person is stored.
        @param  $bdate can be false or a string formatted YYYY-MM-DD
    **/
    public static function computeRawPersonDir($id, $slug, $bdate){
        if($bdate === false){
            return self::getRawPersonBaseDir() . DS . 'no-date';
        }
        [$y, $m, $d] = explode('-', $bdate);
        return self::getRawPersonBaseDir() . DS . $y . DS . $m;
    }
    
    // ******************************************************
    /**
        Computes the file name where a raw person is stored.
    **/
    public static function computeRawPersonFilename($id, $slug, $bdate){
        return self::computeRawPersonDir($id, $slug, $bdate) . DS . "$id-$slug.json";
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
