<?php
/******************************************************************************
    CPara = "Comité para" = Comité belge pour l'investigation scientifique des phénomènes réputés paranormaux
    Belgian skeptic organization.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-11-06 20:47:26+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cpara;

use g5\app\Config;
use g5\model\Group;

class CPara {
    
    // *********************** Comité Para unique id ***********************
    /** 
        Computes Comité Para unique ID
        @param  $num        Unique id within Comité Para file
    **/
    public static function cparaId($num){
        return 'CP-' . str_replace(' ', '', $num);
    }
    
    // *********************** Source management ***********************
    
    /** Slug of source corresponding to Comité Para **/
    const SOURCE_SLUG = 'cpara';
    
    /**
        Path to the yaml file containing the characteristics of the source describing Comité Para
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'cpara' . DS . self::SOURCE_SLUG .'.yml';
    
    // *********************** Group management ***********************

    /** Slug of groups related to Comité Para test **/
    const GROUP_SLUG = 'cpara';
    
    /**
        Paths to the yaml file containing the characteristics of comité Para group.
        Relative to directory data/db/group
    **/
    const GROUP_DEFINITION_FILE = 'cpara' . DS. self::GROUP_SLUG . '.yml';
    
    /** Returns a Group object for 1120 sportsmen. **/
    public static function getGroup(): Group {
        return Group::createFromDefinitionFile(self::GROUP_DEFINITION_FILE);
    }
    // *********************** Output files manipulation ***********************
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1976-cpara';
    }
    
} // end class
