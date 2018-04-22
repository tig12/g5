<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    
    Utilities for timezone classes
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2018-04-22 07:47:56+02:00, Thierry Graff : Creation from a split of TZ_fr 
********************************************************************************/

class TZ{
    
    // ******************************************************
    /**
        Returns the offset of a timezone at a given date in format "sHH:MM" (s = "+" or "-")
        @param  $date ISO 8601 date, like '2018-04-22'
        @param  $zone A zone identifier, like 'Europe/Paris'
    **/
    public static function offset($date, $zone){
        $tz = new DateTimeZone($zone);
        $dt = new DateTime($date);
        $offset = $tz->getOffset($dt);
        $hhmm = TZ::seconds2HHMMSS($offset, true);
        $sign = ($offset < 0 && $hhmm != '00:00') ? '-' : '+';
        return $sign . $hhmm;
    }    
    
    // ******************************************************
    /**
        Converts a nb of seconds to a HH:MM:SS string
        if the nb of second has a decimal part, it is included in the result (ex: 12:28:30.5847441)
        If $secs < 0, its negative sign is ignored, treated as a positive number
        @param  $sec a nb of seconds
        @param  $roundToMinute boolean ; if true, a HH:MM string is returned
    **/
    public static function seconds2HHMMSS($sec, $roundToMinute=false){
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

