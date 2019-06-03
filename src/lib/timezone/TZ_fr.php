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

class TZ_fr{
    
    // ******************************************************
    /**
        Improves php computation of timezone for France.
        - Takes into account the fact that Alsace and a part of Lorraine were german between 1870 and 1918
        - Takes into account the fact that before 1891-03-15, local hour was used
        BUT NOT EXACT :
        - some part of dept 54 was also german between 1871 and 1918
            => a precise computation should take into account the precise local situation
        - Some parts of depts 06, 04, 26, 74, 73 were not french before 1860
        - Between 1940-02 and 1942-11, France was divided in 2 zones (occupied and free)
            => a precise computation should take into account the precise local situation
        @param  $date ISO 8601 date
        @param  $lg longitude in decimal degrees
        @param  $dept Département ("75" for Paris, "01" for Ain etc.)
        @return array with 2 elements : 
                - the timezone offset, format sHH:MM (ex : '-01:00' ; '+00:23')
                - an error message (empty string if no ambiguity)
        @todo   Implementation of computation taking into account the precise local situation
                would need also a latitude parameter
    **/
    public static function offset($date, $lg, $dept){
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
            $hhmm = TZ::seconds2HHMMSS($secs, false);
            $sign = ($lg < 0 && $hhmm != '00:00') ? '-' : '+';
            $offset = $sign . $hhmm;
        }
        else{
            $offset = TZ::offset($date, $zone);
        }
        return [$offset, $err];
    }

    
} // end class

