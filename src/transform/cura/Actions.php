<?php
/********************************************************************************
    Façade for this transformation.
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-09 01:34:14+02:00, Thierry Graff : refactor
********************************************************************************/
namespace g5\transform\cura;

use g5\Datasource;

class Actions implements Datasource{
    
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
        @return A list of possible actions for this data source.
    **/
    public static function getActions(){
        return [
            'raw2csv',
            'addGeo',
            'marked2g55',
        ];
    }
    
    // ******************************************************
    /**
        Routes an action to the appropriate code.
        @return report : string describing the result of execution.
    **/
    public static function action($action, $params=[]){
        switch($action){
        	case 'raw2csv' :
        	    return self::raw2csv($params);
            break;
        	case 'addGeo' :
        	    return self::addGeo($params);
            break;
        	case 'marked2g55' :
        	    return self::marked2g55($params);
            break;
        	default:
        	    throw new Exception("Invalid action : $action");
            break;
        }
    }
    
    // ******************************************************
    /** 
        Converts the parameter to an array of subjects.
        Useful for parameters like 'A' which means everything from A1 to A6.
        Does not perform check on $param.
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
        @return report : string describing the result of execution.
    **/
    private static function raw2csv($params){
        $error_report = "    Possible values : " . implode(', ', self::SUBJECTS_POSSIBLES) . "\n"
                      . "    'A' indicates that all files from A1 to A6 will be processed.";
        if(count($params) == 0){
            return "raw2csv requires a parameter indicating what you want to process.\n" . $error_report;
        }
        if(count($params) > 1){
            return "raw2csv requires a unique parameter indicating what you want to process.\n"
                . "Invalid parameters : " . implode(', ', array_slice($params, 1)) . "\n"
                . $error_report;
        }
        if(!in_array($params[0], self::SUBJECTS_POSSIBLES)){
            return "Invalid parameter '{$params[0]}'\n" . $error_report;
        }
        // OK, the subject is valid.
        $subjects = self::computeSubjects($params[0]);
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
            	    $class = 'g5\transform\cura\A\raw2csv';
                break;
            	case 'D6' :
            	    $class = 'g5\transform\cura\D6\raw2csv';
                break;
            	case 'D10' :
            	    $class = 'g5\transform\cura\D10\raw2csv';
                break;
            	case 'E1' :
            	case 'E3' :
            	    $class = 'g5\transform\cura\E1_E3\raw2csv';
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
        @return report : string describing the result of execution.
        Add geonames.org information to a file.
        Checks parameters and delegates to the correct class.
    **/
    private static function addGeo($params){
        if(count($params) != 1){
            return "ERROR : addGeo accepts only one parameter (" . count($params) . " given)\n";
        }
        if($params[0] != 'D6'){
            return "ERROR : invalid parameter '{$params[0]}' - addGeo can only be executed on file D6.\n";
        }
        return \g5\transform\cura\D6\addGeo::action();
    }
    
}// end class
