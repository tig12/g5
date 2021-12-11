<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-07-02 02:01:09+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Libreoffice {
    
    // ******************************************************
    /**
        Correct the automatic formatting of libreoffice
        @return HH:MM
        @param $
    **/
    public static function fix_hour($str){
        if(is_numeric($str)){
            return str_pad ($str , 2, '0', STR_PAD_LEFT) . ':00';
        }
        // remove seconds (:00) added by libreoffice
        if(strlen($str) == 8){
            return substr($str, 0, 5);
        }
    }
    
    
} // end class
