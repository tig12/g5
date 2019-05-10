<?php
/********************************************************************************
    General code related to Gauquelin5 usage
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-10 08:22:01+02:00, Thierry Graff : new version
********************************************************************************/
namespace g5;

use g5\init\Config;

class G5{
    
    // ******************************************************
    /**
        Returns a list of data sources known by the program
        = list of sub-directories of transform/
    **/
    public static function getDatasources(){
        return array_map('basename', glob(implode(DS, [__DIR__, 'transform', '*']), GLOB_ONLYDIR));
    }
    
    
    // ******************************************************
    /**
        Returns a list of possible actions for a given datasource.
    **/
    public static function getActions($datasource){
        // use of convention described in docs/code-details.html
        $class = "g5\\transform\\$datasource\\Actions";
        return $class::getActions();
    }
}// end class
