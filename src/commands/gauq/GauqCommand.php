<?php
/********************************************************************************
    Implementation of Command interface for gauq namespace.
    This class is needed because user's vocabulary is different from generic mechanism :
    - User can say 'A' to designate all files of serie A.
    - User can say 'E1' or 'E3', and this is handled by sub-package 'E1_E3'.
    So a translation from user's vocabulary to this package's organisation is necessary.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : Creation
    @history    2019-05-09 01:34:14+02:00, Thierry Graff : Refactor
********************************************************************************/
namespace g5\commands\gauq;

use tiglib\patterns\Command;

class GauqCommand implements Command {

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
        
        $possibles = '';
        foreach(array_keys(GauqRouter::DATAFILES_SUBNAMESPACE) as $possible){
            $possibles .= "  - $possible\n";
        }
        
        if(count($params) == 0){
            return "MISSING PARAMETER FOR COMMAND 'gauq'\n"
                . "Possible values for parameter :\n"
                . $possibles;
        }
        
        $datafile = $params[0];
        
        if(!isset(GauqRouter::DATAFILES_SUBNAMESPACE[$datafile])){
            return "MISSING PARAMETER FOR COMMAND 'gauq'\n"
                . "Possible values for parameter :\n"
                . $possibles;
        }
        
        if(count($params) == 1){
            $args3 = GauqRouter::getArgs3($datafile);
            $possibles3 = '';
            foreach($args3 as $possible){
                $possibles3 .= "  - $possible\n";
            }
            return "MISSING PARAMETER FOR COMMAND 'gauq $datafile'\n"
                . "Possible values for parameter :\n"
                . $possibles3;
        }
        
        $command = $params[1];
        
        $datafiles = GauqRouter::computeDatafiles($datafile);
        
        // count is particular, because it is executed once even if the datafile represents several datafiles.
        // So does not loop on datafiles.
        if($command == 'count'){
            $class = "g5\\commands\\gauq\\" . GauqRouter::DATAFILES_SUBNAMESPACE[$datafile] . '\\count';
            return $class::execute($params);
        }
        
        foreach($datafiles as $dtfile){
            // export is available for all datafiles, and implemented in subpackage all.
            if($command == 'export'){
                $class = "g5\\commands\\gauq\\all\\export";
            }
            // tweak2tmp is available for all datafiles, and implemented in subpackage all.
            else if($command == 'tweak2tmp'){
                $class = "g5\\commands\\gauq\\all\\tweak2tmp";
            }
            else{
                $class = "g5\\commands\\gauq\\" . GauqRouter::DATAFILES_SUBNAMESPACE[$datafile] . '\\' . $command;
            }
            $params[0] = $dtfile;
            $report .= $class::execute($params);
        }
        return $report;
    }
    
}// end class
