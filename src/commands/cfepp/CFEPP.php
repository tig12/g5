<?php
/******************************************************************************

    CFEPP = Comité Français pour l’Étude des Phénomènes Paranormaux.
    French skeptic organization.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-11-06 20:51:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp;

use g5\app\Config;

class CFEPP {
    
    // *********************** CFEPP unique id ***********************
    /** 
        Computes CFEPP unique ID
        @param  $num        Unique id within CFEPP file
    **/
    public static function cfeppId($num){
        return $num;
    }
    
    // *********************** Source management ***********************
    
    /** Slug of source corresponding to CFEPP **/
    const SOURCE_SLUG = 'cfepp';
    
    /**
        Path to the yaml file containing the characteristics of the source describing CFEPP
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'cfepp' . DS . self::SOURCE_SLUG .'.yml';
    
    // *********************** Output files manipulation ***********************
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1996-cfepp';
    }
    
} // end class
