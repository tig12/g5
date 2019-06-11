<?php
/******************************************************************************

    @license    GPL
    @history    2019-06-03 12:40:19+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\time;

class HHMM2minutes{
    
    /**
        Converts a HH:MM string (like 12:34) to the number of minutes it represents
        $str can be preceeded by a - (minus sign)
        The separator between hour and minutes can be any non-numeric character
        Hours and / or minutes can be expressed with 1 or 2 digits.
            The following $str will return the same result :
                02:04
                2:04
                02:4
                2:4
        Hours must be between 0 and 23
        Minutes must be between 0 and 59
        @param $str The string to parse
        @return The nb of minutes or false if $str does not correspond to a valid HH:MM string
    **/
    public static function compute($str){
        $pattern = '/^([+-]?)(\d{1,2})\D(\d{1,2})$/';
        preg_match($pattern, $str, $m);
        if(count($m) != 4){
            return false;
        }
        if($m[2] > 23){
            return false;
        }
        if($m[3] > 59){
            return false;
        }
        $res = 60 * $m[2] + $m[3];
        if($m[1] == '-') $res = -$res;
        return $res;
    }
    
}// end class
