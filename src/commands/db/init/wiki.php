<?php
/******************************************************************************
    
    Fills database with information contained in data/wiki
    Addition of wiki project is not done here.
    It must be done before tmp2db steps to permit to build associations between issues and wiki projects.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 17:13:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\model\DB5;
use g5\model\wiki\Wiki as ModelWiki;

class wiki implements Command {
    
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "INVALID USAGE - This command doesn't need parameter:\n";
        }
        $report = '';
        
        return $report;
    }
    
} // end class
