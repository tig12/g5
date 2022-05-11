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
        return 'CF-' . $num;
    }
    
    // *********************** Source management ***********************
    
    /** Slug of source corresponding to CFEPP **/
    const SOURCE_SLUG = 'cfepp';
    
    /**
        Path to the yaml file containing the characteristics of the source describing CFEPP.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'cfepp' . DS . self::SOURCE_SLUG .'.yml';
    
    /** Slug of source corresponding to the CFEPP booklet (publication containing CFEPP test). **/
    const BOOKLET_SOURCE_SLUG = 'cfepp-booklet';
    
    /**
        Path to the yaml file containing the characteristics of the source describing CFEPP booklet.
        Relative to directory data/db/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'cfepp' . DS . self::BOOKLET_SOURCE_SLUG .'.yml';
    
    /** Slug of source corresponding to Jan Willem Nienhuys. **/
    const NIENHUYS_SOURCE_SLUG = 'nienhuys';
    
    /**
        Path to the yaml file containing the characteristics of the source describing Nienhuys.
        Relative to directory data/db/source
    **/
    const NIENHUYS_SOURCE_DEFINITION_FILE = 'cfepp' . DS . self::NIENHUYS_SOURCE_SLUG .'.yml';
    
    // *********************** Group management ***********************

    /** Slug of groups related to final3 **/
    const GROUP_1120_SLUG = 'cfepp-1120';
    const GROUP_1066_SLUG = 'cfepp-1066';
    
    /**
        Paths to the yaml file containing the characteristics of the groups related to final3.
        Relative to directory data/db/group
    **/
    const GROUP_1120_DEFINITION_FILE = 'cfepp' . DS. self::GROUP_1120_SLUG . '.yml';
    const GROUP_1066_DEFINITION_FILE = 'cfepp' . DS. self::GROUP_1066_SLUG . '.yml';
    
    /** Returns a Group object for 1120 sportsmen. **/
    public static function getGroup1120(): Group {
        return Group::createFromDefinitionFile(self::GROUP_1120_DEFINITION_FILE);
    }
    
    /** Returns a Group object for 1066 sportsmen. **/
    public static function getGroup1066(): Group {
        return Group::createFromDefinitionFile(self::GROUP_1066_DEFINITION_FILE);
    }
    
    // *********************** Output files manipulation ***********************
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1996-cfepp';
    }
    
} // end class
