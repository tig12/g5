<?php
/******************************************************************************
    - Offset computation using Olson database (default php behaviour)
    - Utility functions for offset computation
    
    @license    GPL
    @history    2019-06-11 10:27:33+02:00, Thierry Graff : Creation from existing code.
    @history    2020-07-27 14:59:51+02:00, Thierry Graff : add format() and lg2offset()
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset{

    // ******************************************************
    /**
        Returns the offset of a timezone at a given date in format "sHH:MM" or "sHH:MM:HH" (s = "+" or "-")
        The result of this function is coherent with ISO 8601 and php DateTimeZone::getOffset() function.
        It means that UT = LT - offset (universal time = legal time - offset)
        or            LT = UT + offset
        or            offset = LT - UT
        Example : offset::compute('2019-06-20', 'Europe/Paris') = '+02:00'
        
        @param  $date   ISO 8601 date, like '2018-04-22' or '2018-04-22 12:00:00'
                        In fact, this parameter can be any string accepted by php class DateTime constructor.
        @param  $zone   A zone identifier, like 'Europe/Paris'
        @param  $format Format of the returned offset
                        Can be 'HH:MM' or 'HH:MM:SS'
        
        @todo Add tests for $format parameter
    **/
    public static function compute($date, $zone, $format='HH:MM'){
        if($format != 'HH:MM' && $format != 'HH:MM:SS'){
            throw new \Exception("Invalid \$format parameter : $format - Must be 'HH:MM' or 'HH:MM:SS'");
        }
        $tz = new \DateTimeZone($zone);
        $dt = new \DateTime($date);
        $offset = $tz->getOffset($dt);
        return self::format($offset, $format);
    }
    
    // ******************************************************
    /**
        Converts an offset expressed in seconds to sHH:MM or sHH:MM:SS
        @param $seconds Offset expressed in seconds ; can be < 0
        @param $format  'HH:MM' or 'HH:MM:SS'
    **/
    public static function format($seconds, $format){
        $hhmm = ($format == 'HH:MM' ? seconds2HHMMSS::compute($seconds, true) : seconds2HHMMSS::compute($seconds));
        $sign = ($seconds < 0 && $hhmm != '00:00'&& $hhmm != '00:00:00') ? '-' : '+';
        return $sign . $hhmm;
    }
    
    // ******************************************************
    /**
        Converts a longitude to time seconds
        @param $lg Longitude expressed in decimal degrees
    **/
    public static function lg2offset($lg){
        // 240 = 24 * 3600 / 360
        // = nb of time seconds per longitude degree
        return 240 * $lg;
    }
    
}// end class
