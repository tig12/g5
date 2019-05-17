<?php
/******************************************************************************

    @license    GPL
    @history    2019-05-16 12:32:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\init\Config;

class Full{
    
    /** Pattern to check birth date. **/
    const PDATE = '/\d{4}-\d{2}-\d{2}/';
    
    // ******************************************************
    /**
        Returns the full path to sub-directory of 5-tmp/full corresponding too $birthdate
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The full path or false if $birthdate is not formatted correctly.
    **/
    public static function getDirectory($birthdate){
        if(preg_match(self::PDATE, $birthdate) != 1){
            throw new \Exception("Invalid date : $birthdate"); 
        }
        $y = substr($birthdate, 0, 4);
        //$subdir = floor($y / 10) * 10; // one subdir per decade
        $subdir = $y; // one subdir per year
        return Config::$data['dirs']['5-full'] . DS . $subdir;
    }
    /* 
        // test
        $dates = ['2018-12-21', '1790-12-12', '1792-12-12'];
        foreach($dates as $date){
            echo $date . ' : ' . Full::getDirectory($date) . "\n";
        }
    
    */
    
}// end class
