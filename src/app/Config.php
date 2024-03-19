<?php
/********************************************************************************
    Holds config.yml informations.
    
    Config values available via Config::$data
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 09:49:02+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\app;

class Config {
    
    /** Associative array containing config.yml **/
    public static $data = null;
    
    public static function init(){
        $filename = dirname(dirname(__DIR__)) . DS . 'config.yml';
        if(!is_file($filename)){
            echo "MISSING CONFIG FILE : $filename.\n";
            echo "Create this file and try again.\n";
            exit;
        }
        self::$data = @yaml_parse(file_get_contents($filename));
        if(self::$data === false){
            echo "INVALID SYNTAX IN CONFIG FILE.\n";
            echo "Check syntax and try again\n";
            exit;
        }
        
        // Add entries in self::$data['dirs']
        
        self::$data['dirs']['ROOT'] = dirname(dirname(__DIR__));
        
        self::$data['dirs']['raw'] = 'data/raw';
        
        self::$data['dirs']['init'] = 'data/db/init';
        
        self::$data['dirs']['db'] = 'data/db';
    }
    
    
} // end class

