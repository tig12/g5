<?php
/******************************************************************************
    
    Utilities related to export.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-15 11:10:39+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\G5;

class Export {
    
    /** 
        Adds group cardinality in the name of the exported file.
        Ex: change "athletics-competitor.csv" to "athletics-competitor-410.csv"
    **/
    public static function add_number_in_file_name($file, $N){
        $pathinfo = pathinfo($file);
        return $pathinfo['dirname'] . DS . $pathinfo['filename'] . "-$N." . $pathinfo['extension'];
    }
    
    /**
        Handles optional parameters common to all export commands.
        Ex of possible values for $str: "zip=true,sep=false" ; "zip=false" ; "sep=true"
        Uses the format described in class G5.
            zip : should the file be compressed ?
                possible values: true or false
                default value: true
            sep : Should a second file be generated ?
                  This file will be adapted to Solar Fire and Jigsaw (comma used as separator and date fields expressed in separate columns)
                possible values: true or false
                default value: false
            If sep is true, the zip parameter will apply to both generated files
    **/
    public static function computeOptionalParameters($str) {
        $dozip = true;
        $generateSep = false;
        $options = G5::parseOptionalParameters($str);
        if(isset($options['zip'])){
            $dozip = filter_var($options['zip'], FILTER_VALIDATE_BOOLEAN);
        }
        if(isset($options['sep'])){
            $generateSep = filter_var($options['sep'], FILTER_VALIDATE_BOOLEAN);
        }
        return [$dozip, $generateSep];
    }
    
} // end class
