<?php
/******************************************************************************
    Utilities for occupation codes.
    
    @license    GPL
    @history    2020-08-29 21:21:44+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\Config;

Occupation::init();

class Occupation {
    
    private static $records;
    
    /** Loads occupation codes **/
    public static function init(){
        self::$records = yaml_parse_file(self::getDefinitionFile());
    }
    
    /** Returns path to the file containing definition of occupation codes **/
    public static function getDefinitionFile() {
        return Config::$data['dirs']['build'] . DS . 'occu.yml';
    }
    
    /**
        Returns an array containing the codes belonging to a given occupation
        Ex $code="AR" returns ["ACT", "CAR" etc.]
        @param $code
    **/
    public static function getChildren($code){
        $res = [];
        foreach(self::$records as $rec){
            if(!isset($rec['belongs-to'])){
                continue;
            }
            if(in_array($code, $rec['belongs-to'])){
                $res[] = $rec['code'];
            }
        }
        return $res;
    }
    
}// end class
