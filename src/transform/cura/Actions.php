<?php
/********************************************************************************
    Implementation of Command and Dataset interfaces for cura dataset.
    This class is needed because user's vocabulary is different from vocabulary used by the code :
    - User can say 'A' to designate all files of serie A.
    - User can say 'E1' or 'E3', and this is handled by sub-package 'E1_E3'.
    So a translation from user's vocabulary to this package's organisation is necessary.
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
    @history    2019-05-09 01:34:14+02:00, Thierry Graff : refactor
********************************************************************************/
namespace g5\transform\cura;

use g5\patterns\Command;
use g5\patterns\Dataset;

class Actions implements Command, Dataset{
    
    /** 
        Possible values of parameter indicating the subject to process.
    **/
    const DATAFILES_POSSIBLES = [
        'A', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
        // 'B', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
        'D6', 'D10',
        'E1', 'E3',
    ];
    
    /** 
        Associations between datafile in the user's vocabulary and the subpackage that hadnles it
    **/
    const DATAFILES_SUBPACKAGES = [
        'A' => 'A',
        'A1' => 'A',
        'A2' => 'A',
        'A3' => 'A',
        'A4' => 'A',
        'A5' => 'A',
        'A6' => 'A',
        'D6' => 'D6',
        'D10' => 'D10',
        'E1' => 'E1',
        'E3' => 'E3',
    ];
    
    // ******************************************************
    /** 
        Converts the datafile parameter in the user vocabulary to an array of datafiles known by this package.
        Useful for parameters like 'A' which means everything from A1 to A6.
        Does not perform check on $userParam.
        @return Array containing subjects.
    **/
    private static function computeDatafiles($userParam){
        switch($userParam){
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
                return [$userParam];
            break;
        }
    }
    
    
    // ******************************************************
    // Implementation of Dataset
    /**
        Returns an array containing the possible datafiles processed by the dataset.
        @return Array of strings
    **/
    public static function getDatafiles(): array{
        return self::DATAFILES_POSSIBLES;
    }
    
    
    // ******************************************************
    // Implementation of Dataset
    /**
        @return A list of possible actions for this data source.
    **/
    public static function getActions($datafile): array{
        // a clean implementation would use reflection
        $subpackage = self::DATAFILES_SUBPACKAGES[$datafile];
        $tmp = glob(__DIR__ . DS . $subpackage . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $res[] = basename($file, '.php');
        }
        return $res;
    }
    
    
    // ******************************************************
    // Implementation of Command
    /**
        Routes an action to the appropriate code.
        @return report : string describing the result of execution.
    **/
    public static function execute($params=[]): string{
echo "\nexecute : "; print_r($params); echo "</pre>\n"; exit;
// get $action from params + document
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
    
/////////// @todo  parameters checking should not be done here, but by the classes ///////////////////

    // ******************************************************
    /**
        Conversion from files of 1-raw/cura.free.fr to 5-tmp/cura-csv
        Checks parameters and delegates to the correct class.
        @return report : string describing the result of execution.
    **/
    private static function raw2csv($params){
echo "\n<pre>"; print_r($params); echo "</pre>\n";
        $error_report = "    Possible values : " . implode(', ', self::DATAFILES_POSSIBLES) . "\n"
                      . "    'A' indicates that all files from A1 to A6 will be processed.";
        if(count($params) == 0){
            return "raw2csv requires a parameter indicating what you want to process.\n" . $error_report;
        }
        if(count($params) > 1){
            return "raw2csv requires a unique parameter indicating what you want to process.\n"
                . "Invalid parameters : " . implode(', ', array_slice($params, 1)) . "\n"
                . $error_report;
        }
        if(!in_array($params[0], self::DATAFILES_POSSIBLES)){
            return "Invalid parameter '{$params[0]}'\n" . $error_report;
        }
        // OK, the subject is valid.
        $subjects = self::computeDatafiles($params[0]);
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
