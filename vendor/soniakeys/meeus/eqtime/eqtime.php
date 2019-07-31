<?php
/******************************************************************************
    
    Bridge to call go code
    github.com/soniakeys/meeus/eqtime

    @license    GPL
    @history    2019-07-29 12:54:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace soniakeys\meeus\eqtime;

eqtime::compute('2014-07-30');

class eqtime {
    
    // ******************************************************
    /**
        Computes the equation of time for a given date
        @param $date YYYY-MM-DD
        @return Value of the equation of time, in decimal minutes of time (3.5 means 3 minutes 30 seconds)
        
    **/
    public static function compute($date){
        $tmp = explode('-', $date);
        if(count($tmp) != 3){
            throw new \Exception("Invalid date : $date");
        }
        [$y, $m, $d] = $tmp;
        exec("go run eqtime.go $y $m $d", $gores);
        // go build eqtime.go
        // exec("./eqtime $y $m $d", $gores);
        return = (double)$gores[0];
    }
    
    
}// end class
