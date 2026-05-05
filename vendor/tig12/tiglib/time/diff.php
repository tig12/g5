<?php
/******************************************************************************
    
    Computes difference between 2 dates.
    
    @todo       Unit test
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2021-02-25 21:36:31+01:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\time;

class diff {
    
    /**
        Returns the duration from $d1 to $d2 (computes $d2 - $d1).
        If $unit = 'Y' or 'M', the result is based on average month / year values, in days.
        @param  $unit   Unit of the result: 'D', 'M' or 'Y'
        @param  $precision Nb of decimal digits of the result 
        @return Decimal or integer number
    **/
    public static function compute(\DateTime $d1, \DateTime $d2, string $unit = 'Y', int $precision = 0): float {
        // %r: "-" if < 0 ; empty if >= 0
        // %a: nb of days
        $diff = $d1->diff($d2)->format('%r%a');
        
        if($unit == 'Y'){
            $res = $diff / 365.25;
        }
        else if($unit == 'M'){
            $res = $diff / 30.44; // average month length ~ 30.44 days
        }
        else if($unit == 'D'){
            $res = $diff;
        }
        else{
            throw new \Exception("tiglib\\time::diff() : Invalid parameter unit: '$unit' - can be 'Y', 'M' or 'D'");
        }
        return round($res, $precision);
    }
    
    /**
        Returns the duration from $d1 to $d2 (computes $d2 - $d1) in days, months, years.
        @param  $precision Nb of decimal digits of the result 
        @return Associative array containing 3 elements (D, M, Y)
    **/
    public static function compute_all(\DateTime $d1, \DateTime $d2, int $precision = 0): array {
        // %r: "-" if < 0 ; empty if >= 0
        // %a: nb of days
        $diff = $d1->diff($d2)->format('%r%a');
        return [
            'D' => round($diff, $precision),
            'M' => round($diff / 30.44, $precision), // average month length ~ 30.44 days
            'Y' => round($diff / 365.25, $precision),
        ];
    }
    
} // end class

/* 
chatgpt:
Give me a function which computes the number of months between two datetimes, with this signature:
function compute(\DateTime $d1, \DateTime $d2): float

    function compute(\DateTime $d1, \DateTime $d2): float
    {
        // Ensure d1 <= d2
        if ($d1 > $d2) {
            [$d1, $d2] = [$d2, $d1];
        }
    
        $y1 = (int)$d1->format('Y');
        $m1 = (int)$d1->format('n');
        $d1_day = (int)$d1->format('j');
    
        $y2 = (int)$d2->format('Y');
        $m2 = (int)$d2->format('n');
        $d2_day = (int)$d2->format('j');
    
        // Full months difference
        $months = ($y2 - $y1) * 12 + ($m2 - $m1);
    
        // Days in the month of d1
        $daysInMonth = (int)$d1->format('t');
    
        // Fractional month
        $fraction = ($d2_day - $d1_day) / $daysInMonth;
    
        return $months + $fraction;
    }

    function compute_precise(\DateTime $d1, \DateTime $d2): float
    {
        if ($d1 > $d2) {
            [$d1, $d2] = [$d2, $d1];
        }
    
        $months = 0.0;
        $current = clone $d1;
    
        while ($current < $d2) {
            $next = (clone $current)->modify('+1 month');
    
            if ($next <= $d2) {
                $months += 1;
                $current = $next;
            } else {
                $daysTotal = (int)$current->format('t');
                $daysUsed = ($d2->getTimestamp() - $current->getTimestamp()) / 86400;
                $months += $daysUsed / $daysTotal;
                break;
            }
        }
    
        return $months;
    }
*/