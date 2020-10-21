<?php
/********************************************************************************
    Holds config.yml information
    
    Config values available via Config::$data
    
    @license    GPL
    @history    2017-04-27 09:49:02+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5;

class Config{
    
    /**
        Associative array containing config.yml
    **/
    public static $data = null;
    
    
    // ******************************************************
    public static function init(){
        $filename = dirname(__DIR__) . DS . 'config.yml';
        if(!is_file($filename)){
            echo "Unable to read configuration file : $filename.\n";
            echo "Create this file and try again.\n";
            exit;
        }
        self::$data = @yaml_parse(file_get_contents($filename));
        if(self::$data === false){
            echo "Unable to read configuration file.\n";
            echo "Check syntax and try again\n";
            exit;
        }
        // for convenience in certain cases
        self::$data['dirs']['ROOT'] = dirname(__DIR__);
        // Add entries in self::$data['dirs']
        // This is done to avoid to refactor the code.
        // Previously these directory needed to be configured
        // Now it's useless because some data are versioned, and have a imposed location.
        self::$data['dirs']['raw'] = 'data/raw';
        self::$data['dirs']['build'] = 'data/build';
        self::$data['dirs']['model'] = 'data/model';
    }
    
    
}// end class

