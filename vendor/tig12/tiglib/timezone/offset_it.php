<?php
/******************************************************************************
    Computes timezone offset for Italy.
    Sources : THM p 130 and FG p 288 (see README)
    
    WARNING, this computation may be erroneous :
    - THM and FG (see README) differ from Olson database used by php, which can be seen with this php code :
        $tz = new DateTimeZone('Europe/Rome');
        print_r($tz->getTransitions());
    It gives a transition starting at 1901-12-13T20:45:52+0000
    Both books make this transition start at 1893-11-01
    Other transitions seem to correspond (I didn't make an exhaustive check)
    This implementation conforms to THM and FG, not to Olson database
    - For dates prior to 1893-11-01,
    THM says "HLO", (heure locale au soleil vrai) => apparent solar time (AST)
    FG says "heure locale", which means local mean time (LMT)
    We have LMT = AST + E (E = equation of time).
    This implementation conforms to FG (doesn't take E into account).
    - For dates between 1866 and 1893,
    FG  says that the period starts at 1866-09-22
    THM says that the period starts at 1866-11-15
    This implementation conforms to FG.
    
    @license    GPL
    @history    2020-07-25 18:44:49+02:00, Thierry Graff : Creation 
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;
use soniakeys\meeus\eqtime\eqtime;

class offset_it{
    
    // list of provinces
    const SICILIA = ['AG', 'CL', 'CT', 'EN', 'ME', 'PA', 'RG', 'SR', 'TP'];
    
    const SARDINIA = ['CA', 'NU', 'OR', 'SS', 'SU'];
    
    // return codes and messages
    const CASE_ROMA_BEFORE_1885 = 1;
    const MSG_ROMA_BEFORE_1885 = 'Impossible to compute TZ offset for this date in Roma';
    
    const CASE_NOT_ROMA_BEFORE_1866 = 2;
    
    const CASE_BETWEEN_1866_AND_1893 = 3;
    
    const CASE_BETWEEN_1893_AND_1916 = 4;
    
    const CASE_WW2 = 5;
    const MSG_WW2 = 'Possible timezone offset error (german occupation WW2)';
    
    const CASE_PHP_DEFAULT = 6;
    
    // ******************************************************
    /**
        Computation of timezone offset for Italy.
        @param  $date   ISO 8601 formatted "YYYY-MM-DD HH:MM" or "YYYY-MM-DD HH:MM:SS"
        @param  $lg     longitude in decimal degrees
        @param  $c2     Province ("AG" for Agrigento etc.)
        @param  $format Format of the returned offset
                        Can be 'HH:MM' or 'HH:MM:SS'
        
        @return array with 3 elements : 
                - the timezone offset, format sHH:MM (ex : '-01:00' ; '+00:23') 
                  or empty string if unable to compute.
                - an error message ; empty string if offset could be computed without ambiguity.
                - an integer indicating the kind of computation involved (see class constants).
                
        @todo   Implementation of computation taking into account the precise local situation
                would need also a latitude parameter
        @todo   Consider equation of time for dates < 1891-03-15
    **/
    public static function compute($date, $lg, $c2, $format='HH:MM'){
        
        if($format != 'HH:MM' && $format != 'HH:MM:SS'){
            throw new Exception("Invalid \$format parameter : $format - Must be 'HH:MM' or 'HH:MM:SS'");
        }
        
        $err = $offset = '';
        $case = 0;
        
        if($date < '1885-11-15' && $c2 == 'RM'){
            $case = self::CASE_ROMA_BEFORE_1885;
            $err = self::MSG_ROMA_BEFORE_1885;
            return [$offset, $err, $case];
        }
        else if($date < '1866-09-22'){
            // LMT
            $case = self::CASE_NOT_ROMA_BEFORE_1866;
            $offset_sec = offset::lg2offset($lg);
            return [offset::format($offset_sec, $format), $err, $case];
        }
        
        // Here $date >= '1866-09-22'
        
        if($date < '1893-11-01'){
            $case = self::CASE_BETWEEN_1866_AND_1893;
            if(in_array($c2, self::SICILIA)){
                $offset_sec = 3208; // 3208 s = 00:53:28 = Palermo longitude expressed in time seconds
            }
            else if(in_array($c2, self::SARDINIA)){
                $offset_sec = 2184; // 2184 s = 00:36:24 = Cagliari longitude expressed in time seconds
            }
            else{
                // rest of Italy
                $offset_sec = 2996; // 2996 s = 00:49:56 = Roma longitude expressed in time seconds
            }
            return [offset::format($offset_sec, $format), $err, $case];
        }
        
        // Here $date >= '1893-11-01'       
        
        if($date < '1916-06-04 00:00'){
            $case = self::CASE_BETWEEN_1893_AND_1916;
            $offset = 3600;
            return [offset::format($offset, $format), $err, $case];
        }
        
        if($date >= '1943-07' && $date <= '1944-04-02 02:00'){
            // More precise computations possible here
            $case = self::CASE_WW2;
            $err = self::MSG_WW2;
            return [$offset, $err, $case];
        }
        
        // default php behaviour
        $case = self::CASE_PHP_DEFAULT;
        $offset = offset::compute($date, 'Europe/Rome', $format);
        return [$offset, $err, $case];
    }

    
}// end class
