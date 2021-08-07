<?php
/******************************************************************************
    
    Calls export for all occupations of the database.
    By default, the generated files are compressed (using zip).
    
    @license    GPL
    @history    2021-08-07 09:05:28+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\patterns\Command;
use g5\model\Occupation;
use g5\model\Group;

class alloccus implements Command {
    
    /** 
        @param  $params array containing 0 or 1 element :
                        - An optional string "nozip"
        @return An empty string, echoes the results of individual occupations progressively.
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "WRONG USAGE : useless parameter : '{$params[1]}'\n";
        }
        if(count($params) == 1 && $params[0] != 'nozip'){
            return "WRONG USAGE : invalid parameter : '{$params[0]}' - possible value : 'nozip'\n";
        }
        $dozip = true;
        if(count($params) == 1){
            $dozip = false;
        }
        
        $report = '';
        
        $slugsNames = Occupation::getAllSlugNames();
        foreach(array_keys($slugsNames) as $slug){
            $file = Occupation::DOWNLOAD_BASEDIR . DS . $slug . '.csv';
            echo occu::execute([$slug, $file]);
            // uses the fact that groups are named using the occupation slug (see command occugroup).
            $g = Group::getBySlug($slug);
            // uses the fact that occu::execute() is called without 'nozip' parameter
            $g->data['download'] = "$file.zip";
            $g->update(updateMembers:false);
        }
        
        return $report;
    }
    
} // end class
