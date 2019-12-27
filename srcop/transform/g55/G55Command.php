<?php
/********************************************************************************
    Implementation of Command interface for g55 (Gauquelin 1955) dataset.
    This class is needed because user's vocabulary is different from generic mechanism :
    User types something like : php run-g5.php g55 570SPO marked2generated
    This means :
        dataset = g55
        datafile = 570SPO
        action = marked2generated
    This would oblige to have one sub-package per datafile,
    which would be stupid as actions are the same for all datafiles.
    So all actions were put in sub-package 'all'

    @license    GPL
    @history    2019-06-21 09:35:47+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\g55;

use g5\patterns\Command;

class G55Command implements Command{

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
        
/* 
        if($datafile == 'all' && $command == 'all'){
            return \g5\transform\g55\all\all::execute($params);
        }
*/
        
        $datafiles = G55Router::computeDatafiles($datafile);
        
        foreach($datafiles as $dtfile){
            $class = "g5\\transform\\g55\\all\\$command";
            $params[0] = $dtfile;
            $report .= $class::execute($params);
        }
        return $report;
    }
    
}// end class
