<?php
/******************************************************************************
    
    Utilities related to export
    
    @license    GPL
    @history    2021-08-15 11:10:39+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

class Export {
    
    /** 
        Computes an ISO 8601 date formatted YYYY-MM-DD HH:MM:SSsHH:MM
        Ex 1863-07-07 04:00:00-09:53
        @param  $date   ISO 8601 date formatted YYYY-MM-DD HH:MM:SS
        @param  $dateUT ISO 8601 date formatted YYYY-MM-DD HH:MM:SS
        @param  $tzo    ISO 8601 timezone offset formatted sHH:MM
    **/
    public static function exportDate(string|null $date='', string|null $dateUT='', string|null $tzo=''): string {
        if($date != '' && $tzo != ''){
            return "$date$tzo";
        }
        if($date == '' && $dateUT != ''){
            return $dateUT;
        }
        throw new \Exception(
            "exportDate unable to compute\n"
            . "  date = $date\n"
            . "  dateUT = $dateUT\n"
            . "  tzo = $tzo\n"
        );
    }
    
} // end class
