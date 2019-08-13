<?php
/******************************************************************************
    
    Bridge to call go code
    github.com/soniakeys/meeus/eqtime

    @license    GPL
    @history    2019-07-29 12:54:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace soniakeys\meeus\eqtime;

/* 
echo "883 CYC 1884-04-07 Seres Georges     21:04 | 21:00 -> ";
echo eqtime::compute('1884-04-07') . "\n";

echo "1934 RUG 1890-04-22 Lerou Roger      22:55 | 23:00 -> ";
echo eqtime::compute('1890-04-22') . "\n";
                                                                                               
echo "2044 TEN 1886-12-12 Blanchy François 08:55 | 09:00 -> ";
echo eqtime::compute('1886-12-12') . "\n";

echo "2083 TIR 1876-05-29 Parmentier André 03:55 | 04:00 -> ";
echo eqtime::compute('1876-05-29') . "\n";
*/

class eqtime {
    
    // ******************************************************
    /**
        Computes the equation of time for a given date
        @param $date YYYY-MM-DD
        @return Value of the equation of time, in decimal seconds of time
                ex : 125.3 means 2 minutes + 5.3 seconds
    **/
    public static function compute($date){
        $tmp = explode('-', $date);
        if(count($tmp) != 3){
            throw new \Exception("Invalid date : $date");
        }
        [$y, $m, $d] = $tmp;
        //$gofile = __DIR__ . DIRECTORY_SEPARATOR . 'eqtime.go';
        //exec("go run $gofile $y $m $d", $gores);
        // alternative way, if go code is compiled :
        // go build eqtime.go
        $execfile = __DIR__ . DIRECTORY_SEPARATOR . 'eqtime';
        exec("$execfile $y $m $d", $gores);
//        echo "\n"; print_r($gores); echo "\n";;
        return (double)$gores[0];
    }
    
    
}// end class
