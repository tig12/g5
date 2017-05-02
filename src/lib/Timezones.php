<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    
    Timezone matters that are not covered by php
    
    @license    GPL
    @copyright  jetheme.org
    @history    2017-01-03 00:09:55+01:00, Thierry Graff : Creation 
********************************************************************************/

class FrenchTimezone{
    
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
            $hhmm = DateUtils::seconds2HHMMSS($secs, true);
            $sign = $lg < 0 && $hhmm != '00:00' ? '-' : '+';
        }
        else{
            $tz = new DateTimeZone($zone);
            $dt = new DateTime($date);
            $offset = -$tz->getOffset($dt);
            $hhmm = DateUtils::seconds2HHMMSS($offset, true);
            $sign = $offset < 0 && $hhmm != '00:00' ? '-' : '+';
        }
        return [$sign . $hhmm, $err];
    }
    
    
} // end class



