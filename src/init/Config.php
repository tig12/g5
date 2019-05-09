<?php
/** 
    Manages config.yml
    Config available via Config::$data
    @history    2017-04-27 09:49:02+02:00, Thierry Graff : Creation
**/
namespace g5\init;

class Config{
    
    /**
        Associative array containing config.yml
    **/
    public static $data = null;
    
    
    // ******************************************************
    public static function init(){
        $filename = dirname(dirname(__DIR__)) . DS . 'config.yml';
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
    }
    
    
}// end class

