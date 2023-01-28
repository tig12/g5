<?php
/********************************************************************************
    Adds all wiki birth certificates in database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 17:20:13+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\model\wiki\BC;
use tiglib\patterns\Command;
use tiglib\filesystem\globRecursive;

class addall implements Command {
    
    /** 
        @param  $params array with one element:
                        'full' or 'small', indicating the kind of report returned by this command.
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE - This command needs one parameter:\n"
                . "  - small : echoes a minimal report\n"
                . "  - full : echoes a detailed report\n";
        }
        $reportType = $params[0];
        $report =  "--- wiki bc addall $reportType ---\n";
        
        $files = globRecursive::execute(BC::rootDir() . DS . '*BC.yml');
        
        $N = 0;
        foreach($files as $file){
            // Command wiki bc add must be called with project slug
            $tmp = explode(DS, $file);
            $slug = $tmp[count($tmp)-2];
            $report_add = add::execute([$slug]);
            if($reportType == 'full'){
                $tmp = explode("\n", $report_add);
                $report .= $tmp[1] . "\n";
            }
            $N++;
        }
        if($reportType == 'full'){
            $report .= "---------\n";
        }
        $report .= "Inserted $N birth certificates in database\n";
        return $report;
    }
    
} // end class    
