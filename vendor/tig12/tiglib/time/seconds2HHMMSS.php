<?php
/******************************************************************************
    Converts a nb of seconds to a HH:MM:SS string.

    @license    GPL
    @history    2019-06-11 10:29:45+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace tiglib\time;

class seconds2HHMMSS{

    // ******************************************************
    /**
        Converts a nb of seconds to a HH:MM:SS string.
        if the nb of second has a decimal part, it is included in the result (ex: 12:28:30.5847441).
        If $secs < 0, its negative sign is ignored, treated as a positive number.
        @param  $sec a nb of seconds.
        @param  $roundToMinute boolean ; if true, a HH:MM string is returned.
        @param  $roundToSecond boolean ; only used if $roundToMinute = false.
                if true, rounds to nearest second.
        @return String HH:MM or HH:MM:SS, depending on $roundToMinute.
                If returned format is HH:MM:SS, SS can be an integer or have a decimal part, depending on $roundToSecond.
    **/
    public static function compute($sec, $roundToMinute=false, $roundToSecond=true){
        $sec = abs($sec);
        $h = floor($sec / 3600);
        $remain = $sec - $h * 3600;
        if($roundToMinute){
            $m = round($remain / 60);
            return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }
        $m = floor($remain / 60);
        $s = $remain - $m * 60;
        if($roundToSecond){
            $s = round($s);
        }
        return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
    }
    
}// end class
