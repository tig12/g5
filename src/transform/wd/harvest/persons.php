<?php
/********************************************************************************
    
    From person lists stored in local machine, downloads persons' full data
    
    @license    GPL
    @history    2019-10-31 17:17:16+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\wd\harvest;

use g5\Config;
use g5\patterns\Command;
use g5\transform\wd\Wikidata;
use tiglib\arrays\csvAssociative;
use tiglib\strings\slugify;
use tiglib\misc\dosleep;

class persons implements Command{
    
    // *****************************************
    /** 
        @param $params array with one element :
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        return '';
    }
        
}// end class    
