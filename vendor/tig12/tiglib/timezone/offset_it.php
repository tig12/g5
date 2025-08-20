<?php
/******************************************************************************
    Computes timezone offset for Italy.
    
    Sources : Olson, THM p 130 and FG p 288 (see README).
    
    
    @license    GPL
    @history    2020-07-25 18:44:49+02:00, Thierry Graff : Creation 
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset_it{
    
    // list of provinces
    const SICILIA = ['AG', 'CL', 'CT', 'EN', 'ME', 'PA', 'RG', 'SR', 'TP'];
    
    const SARDINIA = ['CA', 'NU', 'OR', 'SS', 'SU'];
    
    // return codes
    const CASE_OLSON                        = 1;
    const CASE_BEFORE_1866                  = 2;
    const CASE_BETWEEN_1866_AND_1893        = 3;
    const CASE_WW2                          = 4;
    
    const MESSAGES = [
        self::CASE_WW2 => 'Possible timezone offset error (German occupation WW2)',
    ];
    
    
    /**
        Computation of timezone offset for Italy.
        See comment of offset::computeTiglib() for parameters and return.
    **/
    public static function compute(
        string $date,
        float $lg,
        string $c2,
        string $format='HH:MM',
    ): array {
        
        $err = $offset = '';
        $case = 0;
        
        // One rule is not implemented: FG p 288 says that the adoption of Roma local time
        // has been adopted in Roma only on 1885-11-15
        
        // LMT before 1866
        // sources diverge for the limit date
        // - Olson: 1866-11-13
        // - FG:    1866-09-22
        // - THM:   1866-11-15
        // Implementation choice is arbitrary
        if($date < '1866-11-13'){
            $case = self::CASE_BEFORE_1866;
            $offset_seconds = offset::lg2offset($lg);
            $offset = offset::format($offset_seconds, $format);
            return [$offset, $case, $err];
        }
        
        // Between 1866 and 1893, FG and THM are coherent.
        // Olson doesn't handle the specificity of Sicilia and Sardinia.
        
        if($date < '1893-11-01'){
            $case = self::CASE_BETWEEN_1866_AND_1893;
            if(in_array($c2, self::SICILIA)){
                $offset_seconds = 3208; // 3208 s = 00:53:28 = Palermo longitude expressed in time seconds
            }
            else if(in_array($c2, self::SARDINIA)){
                $offset_seconds = 2184; // 2184 s = 00:36:24 = Cagliari longitude expressed in time seconds
            }
            else{
                // rest of Italy
                $offset_seconds = 2996; // 2996 s = 00:49:56 = Roma longitude expressed in time seconds
            }
            $offset = offset::format($offset_seconds, $format);
            return [$offset, $case, $err];
        }
        
        // Exclude the cases when DST application were dubious.
        // 1943-07-01 = date of the first city liberated (Palermo)
        // other dates are the limits of application of summer time, from FG, pp 286-288
        
        if(
            ($date >= '1943-07-01' && $date < '1944-04-02 02:00')
         || ($date > '1944-04-03 02:00' && $date < '1944-10-02 03:00')
         || ($date > '1945-04-02 02:00' && $date < '1944-09-17 00:00')
        ){
            // More precise computations possible here
            $case = self::CASE_WW2;
            $err = self::MESSAGES[$case]
            return [$offset, $case, $err];
        }
        
        // Default php behaviour
        $case = self::CASE_OLSON;
        $offset = offset::computeOlson($date, 'Europe/Rome', $format);
        return [$offset, $case, $err];
    }

    
}// end class
