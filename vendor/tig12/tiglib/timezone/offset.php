<?php
/******************************************************************************
    Utility functions for timezone offset computation
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-11 10:27:33+02:00, Thierry Graff : Creation from existing code.
    @history    2020-07-27 14:59:51+02:00, Thierry Graff : add format() and lg2offset()
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset{
    
    /**
        Indicates if timezone computation of a country is implemented by tiglib.
        @param  $country ISO 3166 2-letter code, like 'FR'.
    **/
    public static function isCountryImplemented(string $country): bool {
        return in_array($country, ['FR']);
    }
    
    /**
        Offset computation using tig12\tiglib implementations.
        @param  $country    ISO 3166 2-letter code, like 'FR'
        @param  $date   Legal time, ISO 8601 formatted "YYYY-MM-DD HH:MM" or "YYYY-MM-DD HH:MM:SS"
        @param  $lg     longitude in decimal degrees
        @param  $c2     Province ("AG" for Agrigento etc.)
        @param  $format Format of the returned offset
                        Can be 'HH:MM' or 'HH:MM:SS'
        
        @return array with 3 elements : 
                - the timezone offset, format sHH:MM (ex : '-01:00' ; '+00:23') 
                  or empty string if unable to compute.
                - an integer indicating the kind of computation involved (see class constants).
                - an error message, or empty string if offset could be computed without ambiguity.
        
        TODO This implementation may need to change if {$lg, $c2} is not pertinent for some countries.
    **/
    public static function computeTiglib(
        string $country,
        string $date,
        float $lg,
        string $c2,
        string $format='HH:MM',
    ): array {
    
        if($format != 'HH:MM' && $format != 'HH:MM:SS'){
            throw new \Exception("Invalid \$format parameter : $format - Must be 'HH:MM' or 'HH:MM:SS'");
        }
        
        switch($country){
        	case 'FR': return offset_fr::compute($date, $lg, $c2, $format); break;
        	case 'IT': return offset_it::compute($date, $lg, $c2, $format); break;
        	//case 'BE': return offset_be::compute($date, $lg, $c2, $format); break;
            default: throw new \Exception("$country not handled by offset::compute()");
        }
    }

    /**
        Offset computation using Olson database (default php behaviour).
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
        @return The offset of a timezone at a given date in format "sHH:MM" or "sHH:MM:HH" (s = "+" or "-")
        
        @todo Add tests for $format parameter
    **/
    public static function computeOlson(
        string $date,
        string $zone,
        string $format='HH:MM'
    ): string {
        if($format != 'HH:MM' && $format != 'HH:MM:SS'){
            throw new \Exception("Invalid \$format parameter : $format - Must be 'HH:MM' or 'HH:MM:SS'");
        }
        $tz = new \DateTimeZone($zone);
        $dt = new \DateTime($date);
        $offset = $tz->getOffset($dt);
        return self::format($offset, $format);
    }
    
    /**
        Converts an offset expressed in time seconds to sHH:MM or sHH:MM:SS (s = sign, + or -).
        @param $seconds Offset expressed in time seconds ; can be < 0
        @param $format  'HH:MM' or 'HH:MM:SS'
    **/
    public static function format(int $seconds, string $format): string {
        $hhmm = ($format == 'HH:MM' ? seconds2HHMMSS::compute($seconds, true) : seconds2HHMMSS::compute($seconds));
        $sign = ($seconds < 0 && $hhmm != '00:00'&& $hhmm != '00:00:00') ? '-' : '+';
        return $sign . $hhmm;
    }
    
    /**
        Converts a longitude to time seconds
        @param $lg Longitude expressed in decimal degrees
    **/
    public static function lg2offset(float $lg): float{
        // 240 = 24 * 3600 / 360
        // = nb of time seconds per longitude degree
        return 240 * $lg;
    }
    
}// end class
