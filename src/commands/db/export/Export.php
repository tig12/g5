<?php
/******************************************************************************
    
    Utilities related to export.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-15 11:10:39+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

class Export {
    
    /** 
        Adds group cardinality in the name of the exported file.
        Ex: change "athletics-competitor.csv" to "athletics-competitor-410.csv"
    **/
    public static function add_number_in_file_name($file, $N){
        $pathinfo = pathinfo($file);
        return $pathinfo['dirname'] . DS . $pathinfo['filename'] . "-$N." . $pathinfo['extension'];
    }
    
} // end class
