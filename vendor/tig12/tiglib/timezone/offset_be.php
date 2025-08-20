<?php
/******************************************************************************
    Computes timezone offset for Belgium.
    
    Sources : Olson, THM p 75 and FG p 252 (see README).
    
    @license    GPL
    @history    2025-08-20, Thierry Graff : Creation 
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset_be{
    
    // return codes
    const CASE_OLSON                        = 1;
    const CASE_BEFORE_1880                  = 2;
    
    const MESSAGES = [
    ];
    
    
    /**
        Computation of timezone offset for Italy.
        See comment of offset::computeTiglib() for parameters and return.
        Note: $c2 parameter is useless, as legal time definition in Belgium doesn't have local specificities.
    **/
    public static function compute(
        string $date,
        float $lg,
        string $c2='',
        string $format='HH:MM',
    ): array {
        
        $err = $offset = '';
        $case = 0;
        
        // One rule is not implemented: FG p 288 says that the adoption of Roma local time
        // has been adopted in Roma only on 1885-11-15
        
        // LMT before 1880
        // sources diverge for the limit date
        // - Olson: 1880-01-01 00:00
        // - FG:    1880-01-01 12:00
        // - THM:   1880-01-01 00:00
        // Implementation choice is arbitrary
        if($date < '1880-01-01'){
            $case = self::CASE_BEFORE_1880;
            $offset_seconds = offset::lg2offset($lg);
            $offset = offset::format($offset_seconds, $format);
            return [$offset, $case, $err];
        }
        
        // Default php behaviour
        // The 3 sources diverge during WW1 ; Olson has been used, but this may introduce errors
        $case = self::CASE_OLSON;
        $offset = offset::computeOlson($date, 'Europe/Rome', $format);
        return [$offset, $case, $err];
    }

    
}// end class
