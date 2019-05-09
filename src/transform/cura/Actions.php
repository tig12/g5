<?php
/********************************************************************************
    Router to the different actions of this data source.
    
    @license    GPL
    @history    2019-05-03 17:18:33+02:00, Thierry Graff : creation from a split of class Gauquelin5
********************************************************************************/
namespace g5\transform\cura;

use g5\init\Config;

class Actions{
    
    /** 
        Possible values of parameter indicating the subject to process.
    **/
    const SUBJECTS_POSSIBLES = [
        'A', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
        // 'B', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
        'D6', 'D10',
        'E1', 'E3',
    ];
    
    // ******************************************************
    /** 
        Converts the parameter to an array of subjects.
        Useful for parameters like 'A' which means everything from A1 to A6.
        Does not perform check on $param - check must be done before calling this function.
        @return Array containing subjects.
    **/
    private static function computeSubjects($param){
        switch($param){
        	case 'A' : 
        	    return ['A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
        	break;
        	/* 
        	case 'B' : 
        	    return ['B1', 'B2', 'B3', 'B4', 'B5', 'B6'];
        	break;
        	case 'E2' : 
        	    return ['E2a', 'E2b', 'E2c', 'E2d', 'E2e', 'E2f', 'E2g'];
        	break;
        	*/
            default:
                return [$param];
            break;
        }
    }
    
    // ******************************************************
    /**
        Conversion from files of 1-raw/cura.free.fr to 5-tmp/cura-csv
        Checks parameters and delegates to the correct class.
        @param  $args   Array of parameters, may be empty.
        @return $report String describing the result of execution.
    **/
    public static function cura2csv($args){
        $error_report = "    Possible values : " . implode(', ', self::SUBJECTS_POSSIBLES) . "\n"
                      . "    'A' indicates that all files from A1 to A6 will be processed.";
        if(count($args) == 0){
            return "cura2csv requires a parameter indicating what you want to process.\n" . $error_report;
        }
        if(count($args) > 1){
            return "cura2csv requires a unique parameter indicating what you want to process.\n"
                . "Invalid parameters : " . implode(', ', array_slice($args, 1)) . "\n"
                . $error_report;
        }
        if(!in_array($args[0], self::SUBJECTS_POSSIBLES)){
            return "Invalid parameter '{$args[0]}'\n" . $error_report;
        }
        // OK, the subject is valid.
        $subjects = self::computeSubjects($args[0]);
        // Delegate to the class that will do the job
        $report = '';
        foreach($subjects as $subject){
            switch($subject){
            	case 'A1' : 
            	case 'A2' : 
            	case 'A3' : 
            	case 'A4' : 
            	case 'A5' : 
            	case 'A6' : 
            	    $class = 'g5\transform\cura\A\cura2csv';
                break;
            	case 'D6' :
            	    $class = 'g5\transform\cura\D6\cura2csv';
                break;
            	case 'D10' :
            	    $class = 'g5\transform\cura\D10\cura2csv';
                break;
            	case 'E1' :
            	case 'E3' :
            	    $class = 'g5\transform\cura\E1_E3\cura2csv';
                break;
            }
            if($subject == 'D6' || $subject == 'D10'){
                $report .= $class::action();
            }
            else{
                $report .= $class::action($subject);
            }
        }
        return $report;
    }
    
    // ******************************************************
    /**
    **/
    public static function cura2geo($args){
        if(count($args) != 1){
            return "ERROR : cura2geo accepts only one parameter (" . count($args) . " given)\n";
        }
        if($args[0] != 'D6'){
            return "ERROR : invalid parameter '{$args[0]}' - cura2geo can only be executed on file D6.\n";
        }
        return \g5\transform\cura\D6\cura2geo::action();
    }
    
}// end class
