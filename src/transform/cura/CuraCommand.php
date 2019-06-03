<?php
/********************************************************************************
    Implementation of Command interface for cura dataset.
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

class CuraCommand implements Command{

    // ******************************************************
    /**
        Routes an action to the appropriate command class.
        @param $params Array of parameters
                - the first element contains the datafile to process
                - the second element contains the command to invoke
                - other parameters are transmitted to execute() method of the command
        @return report : string describing the result of execution.
    **/
    public static function execute($params=[]): string{
        if(count($params) < 2){
            $msg = "Invalid call to CuraCommand::execute() - need at least 2 parameters.\n"
                . "Parameters given : " . print_r($params, true) . "\n";
            throw new \Exception($msg);
        }
        $cde_args = $params;
        $datafile = $params[0];
        $command = $params[1];
         // $cde_args = params without datafile and command
        array_shift($cde_args);
        array_shift($cde_args);
        
        switch($command){
        	case 'raw2csv':
        	    return self::raw2csv($datafile, $cde_args);
            break;
        	case 'restoreTime':
        	    $class = "g5\\transform\\cura\\" . Cura::DATAFILES_SUBNAMESPACE[$datafile] . '\restoreTime';
        	    array_unshift($cde_args, $datafile);
        	    return $class::execute($cde_args);
            break;
        	case 'addGeo':
        	    return self::addGeo($datafile, $cde_args);
            break;
        	case 'marked2g55':
        	    return self::marked2g55($datafile, $cde_args);
            break;
        	case 'ertel2csv':
        	    return \g5\transform\cura\A\ertel2csv::execute($params); // HERE $params passed
            break;
            case 'csv2full':
                // HERE tmp code
                return \g5\transform\cura\A\csv2full::execute($params); // HERE $params passed
        	default:
        	    return "g5\transform\cura\CuraCommand - Invalid action : $action";
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
    private static function raw2csv($datafile, $params){
        $error_report = "    Possible values : " . implode(', ', Cura::DATAFILES_POSSIBLES) . "\n"
                      . "    'A' indicates that all files from A1 to A6 will be processed.";
        if(count($params) > 0){
            return "raw2csv requires a unique parameter indicating what you want to process.\n"
                . "Invalid parameters : " . implode(', ', $params) . "\n"
                . $error_report;
        }
        if(!in_array($datafile, Cura::DATAFILES_POSSIBLES)){
            return "Invalid parameter '{$datafile}'\n" . $error_report;
        }
        // OK, the subject is valid.
        $subjects = CuraRouter::computeDatafiles($datafile);
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
                $report .= $class::execute([]);
            }
            else{
                $report .= $class::execute([$subject]);
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
    private static function addGeo($datafile, $params){
        if(count($params) != 0){
            return "ERROR : addGeo accepts only one parameter (" . count($params) . " given)\n";
        }
        if($datafile != 'D6'){
            return "ERROR : invalid parameter '$datafile' - addGeo can only be executed on file D6.\n";
        }
        return \g5\transform\cura\D6\addGeo::execute($params);
    }
    
}// end class
