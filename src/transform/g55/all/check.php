<?php
/********************************************************************************
    
    
    @license    GPL
    @history    2019-05-26 01:32:18+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\g55\all;

use g5\Config;
use g5\patterns\Command;

class check implements Command {
    
    // *****************************************
    /** 
        @param $param Array containing one element (a string)
        @return report
    **/
    public static function execute($params=[]): string{
        
        self::check_newalch($params);
        
    }
    
    
    // ******************************************************
    /**
        @param $
    **/
    private static function check_newalch(){
    }
    
}// end class
                                                                   