<?php
/********************************************************************************
    Implementation of Command interface for cura dataset.
    This class is needed because user's vocabulary is different from generic mechanism :
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
        
        $report = '';
        
        $datafile = $params[0];
        $command = $params[1];
        
        $datafiles = CuraRouter::computeDatafiles($datafile);
        
        // count is particular, because it is executed once even if the datafile represents several datafiles.
        // So does not loop on datafiles.
        if($command == 'count'){
            $class = "g5\\transform\\cura\\" . Cura::DATAFILES_SUBNAMESPACE[$datafile] . '\\count';
            return $class::execute($params);
        }
        
        foreach($datafiles as $dtfile){
            // csv2dl is available for all datafiles, and implemented in subpackage all.
            if($command == 'csv2dl'){
                $class = "g5\\transform\\cura\\all\\csv2dl";
            }
            else if($command == 'tweak2csv'){
                $class = "g5\\transform\\cura\\all\\tweak2csv";
            }
            else{
                $class = "g5\\transform\\cura\\" . Cura::DATAFILES_SUBNAMESPACE[$datafile] . '\\' . $command;
            }
            $params[0] = $dtfile;
            $report .= $class::execute($params);
        }
        return $report;
    }
    
}// end class
