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
    
    /** Name of the csv file in 5-newalch-csv **/
    const TMP_CSV_FILE = '4391SPO.csv';
    
    
    // ******************************************************
    /**
        @return Path to the csv file stored in 5-newalch-csv
    **/
    public static function tmp_csv_filename(){
        return Config::$data['dirs']['5-newalch-csv'] . DS . self::TMP_CSV_FILE;
    }
    
    // ******************************************************
    /**
        Loads file 5-newalch-csv/4391SPO.csv.
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmp_csv_filename());
    }                                                                                              
    
    // ******************************************************
    /**
        Loads file 5-newalch-csv/4391SPO.csv in an asssociative array ; keys = NR
    **/
    public static function loadTmpFile_nr(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['NR']] = $row;
        }
        return $res;
    }
    
}// end class
