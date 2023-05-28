<?php
/******************************************************************************

    CSICOP = Committee for the Scientific Investigation of Claims of the Paranormal
    U.S. skeptic organization.
                                   
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-20 07:33:13+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop;

use g5\model\Group;
use g5\commands\csicop\si42\SI42;
use g5\commands\csicop\irving\Irving;

class CSICOP {
    
    // *********************** CSICOP unique id ***********************
    /** 
        Computes CSICOP unique ID
        @param  $num        Unique id within csicop file
    **/
    public static function csicopId($num){
        return 'CS-' . $num;
    }
    
    // *********************** Source management ***********************
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'csicop';
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'csicop' . DS . self::SOURCE_SLUG . '.yml';
    
    // *********************** Group management ***********************
    
    /** Slug of the group (all csicop records) **/
    const GROUP_SLUG = 'csicop';

    /** Slug of the group in db (canvas 1) **/
    const GROUP1_SLUG = 'csicop-batch1';

    /** Slug of the group in db (canvas 2) **/
    const GROUP2_SLUG = 'csicop-batch2';

    /** Slug of the group in db (canvas 3) **/
    const GROUP3_SLUG = 'csicop-batch3';
    
    /**
        Computes the path to the yaml file containing the characteristics of the groups involved in CSICOP test.
        Relative to directory data/db/group
        @param  $slug   Slug of the considered group ; one of GROUP*_SLUG of this class
    **/
    public static function groupDefinitionFile($slug): string {
        return 'csicop' . DS . $slug . '.yml';
    }
    
    /** Returns a Group object for CSICOP 408 sportsmen. **/
    public static function getGroup(): Group {
        return Group::createFromDefinitionFile(self::groupDefinitionFile(self::GROUP_SLUG));
    }
    
    /** Returns a Group object for CSICOP Batch 1 (128 athletes). **/
    public static function getGroup_batch1(): Group {
        return Group::createFromDefinitionFile(self::groupDefinitionFile(self::GROUP1_SLUG));
    }
    
    /** Returns a Group object for CSICOP Batch 2 (198 athletes). **/
    public static function getGroup_batch2(): Group {
        return Group::createFromDefinitionFile(self::groupDefinitionFile(self::GROUP2_SLUG));
    }
    
    /** Returns a Group object for CSICOP Batch 3 (82 athletes). **/
    public static function getGroup_batch3(): Group {
        return Group::createFromDefinitionFile(self::groupDefinitionFile(self::GROUP3_SLUG));
    }
    
    // *********************** Output files manipulation ***********************
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1979-csicop';
    }
    
} // end class
