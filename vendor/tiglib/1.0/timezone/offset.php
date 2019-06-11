<?php
/******************************************************************************

    @license    GPL
    @history    2019-06-11 10:27:33+02:00, Thierry Graff : Creation from existing code.
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset{

    // ******************************************************
    /**
        Returns the offset of a timezone at a given date in format "sHH:MM" (s = "+" or "-")
        @param  $date ISO 8601 date, like '2018-04-22'
        @param  $zone A zone identifier, like 'Europe/Paris'
    **/
    public static function compute($date, $zone){
        $tz = new \DateTimeZone($zone);
        $dt = new \DateTime($date);
        $offset = $tz->getOffset($dt);
        $hhmm = seconds2HHMMSS::compute($offset, true);
        $sign = ($offset < 0 && $hhmm != '00:00') ? '-' : '+';
        return $sign . $hhmm;
    }    
    
}// end class
