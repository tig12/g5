<?php
/********************************************************************************
    Constants and utilities related to all Erel files.
    
    @license    GPL
    @history    2021-11-06 18:39:04+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel;

use g5\app\Config;

class Ertel {
        
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
