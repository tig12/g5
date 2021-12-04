<?php
/******************************************************************************

    CFEPP = Comité Français pour l’Étude des Phénomènes Paranormaux.
    French skeptic organization.

    @license    GPL
    @history    2021-11-06 20:51:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp;

use g5\app\Config;

class CFEPP {
    
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
