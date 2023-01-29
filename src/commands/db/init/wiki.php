<?php
/******************************************************************************
    
    Fills database with information contained in data/wiki
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 17:13:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\DB5;

use g5\commands\wiki\project\addall     as projectAddAll;
use g5\commands\wiki\bc\addall          as bcAddAll;

class wiki implements Command {
    
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "INVALID USAGE - This command doesn't need parameter:\n";
        }
        
        $report = "--- db init wiki ---\n";
        
        $dblink = DB5::getDbLink();
        
        // insert all wiki projects - must be done before BCs
        $report .= projectAddAll::execute(['small']);
        
        // insert all BCs
        $report .= bcAddAll::execute(['small']);
        
        return $report;
    }
    
} // end class
