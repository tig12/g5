<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-11-17 02:01:52+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;

class look implements Command {                                                                       
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = SI42::tmpFilename();
        
        $report =  "Looking file $infile\n";
        $rows = csvAssociative::compute($infile);
        
        $dateMax = '000-00-00';
        $dateMin = '999-99-99';
        $datesExtreme = [];
        $n50 = 0; // nb dates > 1950
        $states = [];
        
        foreach($rows as $row){
            if($row['DATE'] < $dateMin){
                $dateMin = $row['DATE'];
            }
            if($row['DATE'] > $dateMax){
                $dateMax = $row['DATE'];
            }
            if($row['DATE'] < '1900'){
                $datesExtreme[] = $row['DATE'];
            }
            if($row['DATE'] > '1950'){
                $n50++;
                $datesExtreme[] = $row['DATE'];
            }
            $state = $row['C2'];
            if(!isset($states[$state])){
                $states[$state] = 0;
            }
            $states[$state]++;
        }
        ksort($states);
        sort($datesExtreme);
        
        $report .= "--- States ---\n";
        foreach($states as $state => $nb){
            $report .= "  $state : $nb\n";
        }
        $report .= "--- Dates ---\n";
        $report .= "Min max : $dateMin - $dateMax\n";
        $report .= "Extreme dates :\n";
        foreach($datesExtreme as $date){
            $report .= "$date  ";
        }
        $report .= "\n$n50 dates > 1950\n";
        return $report;
    }
    
}// end class    

