<?php
/******************************************************************************
    
    Contains constants related to wikidata.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-16 12:58:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wd;

use g5\app\Config;

class Wikidata{
    
    /**
        Directory containing versioned informations, relative to g5 root dir
    **/
    const INPUT_DIR = 'data/db/wikidata';
    
    /**
        Directory containing unversioned informations, relative to g5 root dir
    **/
    const TMP_DIR = 'data/tmp/wikidata';
    
    /** Base url to query WDQS **/
    const QUERY_URL = 'https://query.wikidata.org/sparql?format=json&query=';
        
    /**
        Path to the yaml file containing the characteristics of Wikidata information source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'wikidata.yml';
    
    /**
        Slug of Wikidata information source.
    **/
    const SOURCE_SLUG = 'wd';
    
    // ******************************************************
    /**
        Queries WDQS
        @param  $query Query to issue, not url encoded.
        @return Array with 2 elements :
                - The http response code
                - The result of the query ; contains false if response code != 200
    **/
    public static function query($query): array{
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
