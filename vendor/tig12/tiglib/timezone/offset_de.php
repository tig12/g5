<?php
/******************************************************************************
    Computes timezone offset for Germany.
    
    Sources : Olson, THM p 61 and FG p 244 (see README).
    
    @license    GPL
    @history    2025-08-20 12:50:16+02:00, Thierry Graff : Creation 
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset_be{
    
    // return codes
    const CASE_OLSON                        = 1;
    
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
        
        // Default php behaviour
        $case = self::CASE_OLSON;
        $offset = offset::computeOlson($date, 'Europe/Rome', $format);
        return [$offset, $case, $err];
    }

    
}// end class
