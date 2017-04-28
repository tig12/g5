<?php
/** 
    Manages config.yml
    Config available via Config::$data
    @history    2017-04-27 09:49:02+02:00, Thierry Graff : Creation
**/
namespace gauquelin5\init;

class Config{
    
    /**
        Associative array containing config.yml
    **/
    public static $data = null;
    
    
    // ******************************************************
    public static function init(){
        $DIR_ROOT = dirname(dirname(__DIR__));
        self::$data = \YAML::parse($DIR_ROOT . DS . 'config.yml');
    }
    
    
}// end class

