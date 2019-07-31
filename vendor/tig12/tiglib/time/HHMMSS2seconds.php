<?php
/******************************************************************************
    Converts a HH:MM:SS or HH:MM string (like 12:34:45 or 12:34) to the number of seconds it represents.

    @license    GPL
    @history    2019-06-13 17:21:08+02:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\time;

class HHMMSS2seconds{
    
    // ******************************************************
    /**
        Converts a HH:MM:SS or HH:MM string (like 12:34:45 or 12:34) to the number of seconds it represents.
        $str can be preceeded by a - (minus sign)
        The separator between hour and minutes can be any non-numeric character
        @param  $str  The string to parse
        @return The nb of seconds or false if bad format.
    **/
    public static function compute($str){
        // if format is HH:MM, add ":00" to $str
        $pattern = '/^([+-]?)(\d{1,2})\D(\d{1,2})$/';
        preg_match($pattern, $str, $m);
        if(count($m) == 4){
            $str .= ':00';
        }
        // case HH:MM:SS
        $pattern = '/([+-]?)(\d{1,2})\D(\d{1,2})\D(\d{1,2})/';
        preg_match($pattern, $str, $m);
        if(count($m) != 5){
            return false;
        }
        if($m[2] > 23){
            return false;
        }
        if($m[3] > 59){
            return false;
        }
        if($m[4] > 59){
            return false;
        }
        $res = 3600 * $m[2] + 60 * $m[3] + $m[4];
        if($m[1] == '-') $res = -$res;
        return $res;
    }
}// end class
