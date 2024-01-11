<?php
/******************************************************************************
    
    Recomputes completely table search used by ajax, for all persons stored in database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-22 15:30:56+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\Search as ModelSearch;

class search implements Command {
    
    /** 
        @param  $params empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "USELESS PARAMETER {$params[0]}\n";
        }
        $report = "--- db init search ---\n";
        $report .= ModelSearch::addAllPersons();
        return $report;
    }
    
} // end class
