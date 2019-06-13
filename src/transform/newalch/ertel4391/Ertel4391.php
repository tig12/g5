<?php
/******************************************************************************
    Code common to ertel4391
    
    @license    GPL
    @history    2019-05-11 23:15:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\Config;
use tiglib\arrays\csvAssociative;

class Ertel4391{
    
    /** Name of the csv file in 5-tmp/newalch-csv **/
    const TMP_CSV_FILE = '4391SPO.csv';
    
    
    // ******************************************************
    /**
        Loads file 5-tmp/newalch-csv/4391SPO.csv.
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::TMP_CSV_FILE);
    }                                                                                              
    
}// end class
