<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    
    Timezone matters specific to France that are not covered by php
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2017-01-03 00:09:55+01:00, Thierry Graff : Creation 
    @history    2017-05-04 10:38:22+02:00, Thierry Graff : Adaptation for autonom use 
********************************************************************************/

class FrenchTZ{
    
    // ******************************************************
    /**
        Improves php computation of timezone for France.
        - Takes into account the fact that Alsace and a part of Lorraine were german between 1870 and 1918
        - Takes into account that before 1891-03-15, local hour was used
        BUT NOT EXACT :
        - some part of dept 54 was also german between 1871 and 1918
            => a precise computation should take into account the precise local situation
        - Some parts of depts 06, 04, 26, 74, 73 were not french before 1860
        - Between 1940-02 and 1942-11, France was divided in 2 zones (occupied and free)
            => a precise computation should take into account the precise local situation
        @param  $date ISO 8601 date
        @param  $lg longitude in decimal degrees
        @return array with 2 elements : 
                - the timezone offset, format sHH:MM (ex : '-01:00' ; '+00:23')
                - an error message
        @todo   Implementation of computation taking into account the precise local situation
                would need also a latitude parameter
    **/
    public static function offset_fr($date, $lg, $dept){
        $err = '';
        $zone = 'Europe/Paris';
        if($date < '1918-11-11' && $date > '1871-02-15'){
            if(in_array($dept, [67, 68, 57])){
                $zone = 'Europe/Berlin';
                $err = "Possible timezone offset error (german zone) : $date $dept";
            }
            if($dept == '54'){
                $err = "Possible timezone offset error (dept 54) - check precise local conditions : $date $dept";
            }
        }
        if($date >= '1940-02' && $date <= '1942-11'){
            $err = "Possible timezone offset error (WW2) - check precise local conditions : $date";
        }
        if($date < '1891-03-15'){
            // hour = HLO, local hour at real sun
            $secs = 240 * $lg; // 240 = 3600 / 15 = nb of time seconds per longitude degrees
            $hhmm = self::seconds2HHMMSS($secs, true);
            $sign = ($lg < 0 && $hhmm != '00:00') ? '-' : '+';
        }
        else{
            $tz = new DateTimeZone($zone);
            $dt = new DateTime($date);
            $offset = -$tz->getOffset($dt);
            $hhmm = self::seconds2HHMMSS($offset, true);
            $sign = ($offset < 0 && $hhmm != '00:00') ? '-' : '+';
        }
        return [$sign . $hhmm, $err];
    }
    
    
    // ******************************************************
    /**
        Converts a nb of seconds to a HH:MM:SS string
        if the nb of second has a decimal part, it is included in the result (ex: 12:28:30.5847441)
        If $secs < 0, its negative sign is ignored, treated as a positive number
        @param  $sec a nb of seconds
        @param  $roundToMinute boolean ; if true, a HH:MM string is returned
    **/
    private static function seconds2HHMMSS($sec, $roundToMinute=false){
        $sec = abs($sec);
        $h = floor($sec / 3600);
        $remain = $sec - $h * 3600;
        if($roundToMinute){
            $m = round($remain / 60);
            return self::addZeroes($h) . ':' . self::addZeroes($m);
        }
        $m = floor($remain / 60);
        $s = $remain - $m * 60;
        return self::addZeroes($h) . ':' . self::addZeroes($m) . ':' . self::addZeroes($s);
    }
    
    
    //***************************************************
    /**
        Formats a positive number, considered as a string.
        Adds zeroes in front of $nb to get a string of length $size.
        Ex : <code>addZeroes(92, 4)</code> returns "0092".
    **/
    private static function addZeroes($str, $size=2){
        return str_pad($str, $size, '0', STR_PAD_LEFT);
    }
    
    
} // end class

