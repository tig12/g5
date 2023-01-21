<?php
/********************************************************************************
    Adds a wiki project in database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-01-21 19:48:53+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\project;

use g5\model\wiki\Project;
use tiglib\patterns\Command;

class add implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the project to add ; ex: french-math
                    This slug must correspond to an existing <slug>.yml file in data/wiki/project/
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE: This commands needs one parameter: the slug of the project to add\n";
        }
        $slug = $params[0];
        try{
            Project::addOne($slug);
        }
        catch(\Exception $e){
            return $e->getMessage() . "\n";
        }
        $report =  "--- wiki project add $slug ---\n";
        $report .= "Added project $slug in database\n";
        return $report;
    }
    
} // end class    
