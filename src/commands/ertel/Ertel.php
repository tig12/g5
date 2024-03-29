<?php
/********************************************************************************
    Constants and utilities related to all Erel files.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-11-06 18:39:04+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel;

use g5\app\Config;

class Ertel {
            
    // *********************** Source management ***********************
    
    /** Slug of source corresponding to Suitbert Ertel **/
    const SOURCE_SLUG = 'ertel';
    
    /**
        Path to the yaml file containing the characteristics of the source describing Suitbert Ertel
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'ertel' . DS . self::SOURCE_SLUG .'.yml';
    
    
    // *********************** Ertel unique id ***********************
    /** 
        Computes Ertel ID
        @param  $fileCode   Code of Ertel file
                            - 'S' for 4384 sportsmen - currently only possible value
        @param  $num        Unique id within the file
    **/
    public static function ertelId($fileCode, $num){
        return 'E' . $fileCode . '-' . $num;
    }
    
    /**
        Converts an Ertel id to an array containing 2 elements: a "file code", identifying the file and the num within this file.
        Ex: "ES-654" returns ["ES", 654]
        @param $erId    String like 'ES-654'
    **/
    public static function explodeErtelId($erId){
        return explode('-', $erId);
    }
        
    
    
    // *********************** Raw files manipulation ***********************
    
    /** 
        Computes the name of the directory where raw Ertel files are stored
    **/
    public static function rawDirname(){
        return Config::$data['dirs']['raw'] . DS . 'ertel';
    }
    
    // *********************** Tmp files manipulation ***********************
        
    /** 
        Computes the name of the directory where tmp Ertel files are stored
    **/
    public static function tmpDirname(){
        return Config::$data['dirs']['tmp'] . DS . 'ertel';
    }
    
} // end class
