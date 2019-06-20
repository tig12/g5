<?php
/******************************************************************************
    Improves php computation of timezone for France.

    @license    GPL
    @history    2017-01-03 00:09:55+01:00, Thierry Graff : Creation 
    @history    2017-05-04 10:38:22+02:00, Thierry Graff : Adaptation for autonom use 
    @history    2019-06-11 10:41:23+02:00, Thierry Graff : Integration to tiglib
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;

class offset_fr{
    
    // ******************************************************
    /**
        Improves php computation of timezone offset for France.
        - Takes into account the fact that Alsace and a part of Lorraine were german between 1870 and 1918
        - Takes into account the fact that before 1891-03-15, local hour was used
        BUT NOT EXACT :
        - some part of dept 54 was also german between 1871 and 1918
            => a precise computation should take into account the precise local situation
        - Some parts of depts 06, 04, 26, 74, 73 were not french before 1860
        - Between 1940-02 and 1942-11, France was divided in 2 zones (occupied and free)
            => a precise computation should take into account the precise local situation
        Computations are based on "Traité de l'heure dans le monde", 5th edition, 1990
        @param  $date ISO 8601 date
        @param  $lg longitude in decimal degrees
        @param  $dept Département ("75" for Paris, "01" for Ain etc.)
        @param  $format Can be 'HH:MM' or 'HH:MM:SS'
        
        @return array with 3 elements : 
                - the timezone offset, format sHH:MM (ex : '-01:00' ; '+00:23') 
                or empty string if unable to compute.
                - an error message ; empty string if offset could be computed without ambiguity.
                - an integer indicating the kind of computation involved (see code, variable $case).
                
        @todo   Implementation of computation taking into account the precise local situation
                would need also a latitude parameter
        @todo   Consider time equation for dates < 1891-03-15
    **/
    public static function compute($date, $lg, $dept, $format='HH:MM'){
        if($format != 'HH:MM' && $format != 'HH:MM:SS'){
            throw new Exception("Invalid \$format parameter : $format - Must be 'HH:MM' or 'HH:MM:SS'");
        }
        $err = $offset = '';
        $case = 0;
        
        if($date > '1871-02-15' && $date < '1918-11-11'){
            // why 1871-02-15 and not 1871-05-10 ?
            /* 
            http://abreschviller.fr/LA-GUERRE-FRANC-PRUSSIENNE-ET-L-ANNEXION
            Signé le 10 Mai 1871 à Francfort, le traité de Paix enlevait à la France
            67, 68 : Alsace,
            57 : Moselle (l’ensemble du département, exception faite de l’arrondissement de Briey),
            54 et 57 : un tiers de la Meurthe (les arrondissements de Sarrebourg et Château-Salins)
            88 : Vosges (la vallée de la Bruche, de Schirmeck à Saales).             
            */
            if(in_array($dept, [54, 57, 88])){
                $case = 1;
                $err = "Possible timezone offset error (german zone 1871 - 1918 ; dept 54, 57, 88) : $date $dept";
            }
            else if(in_array($dept, [67, 68])){
                $case = 2;
                //$zone = 'Europe/Berlin';
                // @todo Possible to compute
                $err = "Possible timezone offset error (german zone 1871-1918 ; dept 67, 68) : $date $dept";
            }
        }                                                         
        if($date >= '1940-02' && $date <= '1942-11'){
            $case = 3;
            $err = "Possible timezone offset error (german occupation WW2) : $date";
        }
        
        if($err != ''){
            return [$offset, $err, $case];
        }
        
        if($date < '1891-03-15'){
            $case = 4;
            // hour = HLO, local hour at real sun
            // HLOM = HLO + E ******** Here should consider time equation ********
            // HLOM = UT + Lg
            // offset = LT - UT
            // => offset = Lg
            $secs = 240 * $lg; // 240 = 24 * 3600 / 360 = nb of time seconds per longitude degree
            $hhmmss = $format == 'HH:MM' ? seconds2HHMMSS::compute($secs, true) : seconds2HHMMSS::compute($secs);
            //$sign = ($lg < 0 && $hhmmss != '00:00') ? '-' : '+';
            $sign = ($lg >= 0) ? '+' : '-';
            $offset = $sign . $hhmmss;
        }
        else{
            $case = 5;
            $offset = offset::compute($date, 'Europe/Paris', $format);
        }
        
        return [$offset, $err, $case];
    }

    
}// end class
