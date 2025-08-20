<?php
/******************************************************************************
    Computation of timezone offset for France.
    
    Sources : Olson, THM p 104 and FG p 265 (see README).
    
    - Takes into account the fact that Alsace and a part of Lorraine were German between 1870 and 1918
    - Takes into account the fact that before 1891-03-15, local hour was used
    BUT NOT EXACT :
    - some part of dept 54 was also german between 1871 and 1918
        => a precise computation should take into account the precise local situation
    - Some parts of depts 06, 04, 26, 74, 73 were not french before 1860
    - Between 1940-02 and 1942-11, France was divided in 2 zones (occupied and free)
      => a precise computation should take into account the precise local situation

    @license    GPL
    @history    2017-01-03 00:09:55+01:00, Thierry Graff : Creation
    @history    2017-05-04 10:38:22+02:00, Thierry Graff : Adaptation for autonom use
    @history    2019-06-11 10:41:23+02:00, Thierry Graff : Integration to tiglib
    @history    2021-12-19 00:53:48+01:00, Thierry Graff : Use Françoise Gauquelin instead of "Traité de l'heure dans le monde"
********************************************************************************/
namespace tiglib\timezone;

use tiglib\time\seconds2HHMMSS;
// use soniakeys\meeus\eqtime\eqtime; // useless if using Françoise Gauquelin definition of local time.

class offset_fr {
    
    // return codes
    const CASE_OLSON                = 1;
    const CASE_1871_1918_LORRAINE   = 2;
    const CASE_1871_1918_ALSACE     = 3;
    const CASE_WW2                  = 4;
    const CASE_WW2_END              = 5;
    const CASE_BEFORE_1891          = 6;
    
    const MESSAGES = [
        self::CASE_1871_1918_LORRAINE =>
            'Timezone offset not computed because of potential error: French or German TZ regime ?'
          . '<br>1871-05-10 - 1918-11-11: départements 54, 57, 88 were partially occupied by Germany.',
        self::CASE_1871_1918_ALSACE   =>
            'Timezone offset not computed by offset_fr - must be done by offset_de.'
          . '<br>1871-05-10 - 1918-11-11: départements 67, 68 were under German timezone regime.',
        self::CASE_WW2                =>
            'Timezone offset not computed because of potential error: French or German TZ regime ?'
          . '<br>1940-02 - 1942-11-02: WW2 - Timezone offset depends on the date of occupation of birth place by Germany.',
        self::CASE_WW2_END            =>
            'Timezone offset not computed because of potential error: French or German TZ regime ?'
          . '<br>1944-06-06 - 1945-09-16: WW2 - Officially German time was abolished 1945-09-16 but some cities changed their time just after their liberation',
    ];
    
    /**
        Computation of timezone offset for France.
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
        
        if($date > '1871-05-10' && $date < '1918-11-11'){
            // 1871-05-10 comes from FG p 269
            // http://abreschviller.fr/LA-GUERRE-FRANC-PRUSSIENNE-ET-L-ANNEXION
            // Signé le 10 Mai 1871 à Francfort, le traité de Paix enlevait à la France
            // 67, 68 : Alsace,
            // 57 : Moselle (l’ensemble du département, exception faite de l’arrondissement de Briey),
            // 54 et 57 : un tiers de la Meurthe (les arrondissements de Sarrebourg et Château-Salins)
            // 88 : Vosges (la vallée de la Bruche, de Schirmeck à Saales).             
            if(in_array($c2, [54, 57, 88])){
                // See FG p 269
                // This case could be computed using coordinates of the limit of occupied zone.
                $case = self::CASE_1871_1918_LORRAINE;
                $err = self::MESSAGES[$case] . " - dept $c2 - $date";
            }
            else if(in_array($c2, [67, 68])){
                // zone = 'Europe/Berlin';
                $case = self::CASE_1871_1918_ALSACE;
                $err = self::MESSAGES[$case] . " - dept $c2 - $date";
            }
        }
        if($date >= '1940-02' && $date <= '1942-11-02'){
            // Check 1940-02 - FG says 1940-06
            $case = self::CASE_WW2;
            $err = self::MESSAGES[$case] . " : $date";
        }
        if($date >= '1944-06-06' && $date <= '1945-09-16'){
            // See FG p 269
            $case = self::CASE_WW2_END;
            $err = self::MESSAGES[$case] . " : $date";
        }
        
        if($err != ''){
            return [$offset, $case, $err];
        }
        
        if($date < '1891-03-15'){
            $case = self::CASE_BEFORE_1891;
            /* 
            // From "Traité de l'heure dans le monde" :
            // legal hour HL = HLO, local hour at real sun
            // and UT = HLO - Lg - E (E = equation of time)
            // definition of offset = HL - UT
            //        = HLO - (HLO - Lg - E)
            //        = Lg + E
            $lg_seconds = 240 * $lg; // 240 = 24 * 3600 / 360 = nb of time seconds per longitude degree
            $eqtime_seconds = eqtime::compute(substr($date, 0, 10));
            $offset_seconds = $lg_seconds + $eqtime_seconds;
            //
            // THIS METHOD USING EQUATION OF TIME WAS ABANDONED
            //
            */
            // From "Problèmes de l'heure résolus pour le monde entier" (F. Gauquelin) :
            // legal hour HL
            // and UT = HL - Lg
            // definition of offset = HL - UT
            //        = HL - (HL - Lg)
            //        = Lg
            $offset_seconds = offset::lg2offset($lg);
            $offset = offset::format($offset_seconds, $format);
        }
        else{
            $case = self::CASE_OLSON;
            $offset = offset::computeOlson($date, 'Europe/Paris', $format);
        }
        
        return [$offset, $case, $err];
    }

    
}// end class
