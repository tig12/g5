<?php
/********************************************************************************
    Adds a wiki project in database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-22 17:20:13+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\project;

use g5\model\wiki\Project;
use tiglib\patterns\Command;

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
        $report =  "--- wiki project addall $reportType ---\n";
        
        $files = glob(Project::rootDir() . DS . '*.yml');
        $N = 0;
        foreach($files as $file){
            // project filename must be called from project slug
            $basename = basename($file);
            $slug = str_replace('.yml', '', $basename);
            try{
                $id = Project::addOne($slug);
            }
            catch(\Exception $e){
                return $e->getMessage() . "\n";
            }
            if($reportType == 'full'){
                $report .= "Added project $slug in database - id = $id\n";
            }
            $N++;
        }
        if($reportType == 'full'){
            $report .= "---------\n";
        }
        $report .= "Inserted $N wiki projects in database\n";
        return $report;
    }
    
} // end class    
