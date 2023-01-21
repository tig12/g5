<?php
/********************************************************************************
    Copies src/model/wiki/project.yml to data/wiki/project
    and renames the destination file from the slug of the project to prepare.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-21 20:36:27+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\project;

use g5\G5;
use g5\model\wiki\Project;
use tiglib\patterns\Command;

class prepare implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the project to prepare ; ex: french-math
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE: This commands needs one parameter: the slug of the project to prepare\n";
        }
        
        $slug = $params[0];
        
        $report =  "--- wiki project prepare $slug ---\n";
                
        $destFile = Project::rootDir() . DS . $slug . '.yml';
        // if $destFile already exists, don't replace it
        if(!is_file($destFile)){
            $sourceFile = implode(DS, [G5::ROOT_DIR, 'model', 'wiki', 'project.yml']);
            copy($sourceFile, $destFile);
            $report .= "Created file $destFile\n";
        }
        return $report;
    }
    
} // end class    

