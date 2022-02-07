<?php
/******************************************************************************
    
    Generate csv files for all occupations of the database.
    
    By default, the generated files are compressed (using zip).
    Calls command db/export/occu for each occupation.
    
    WARNING: The groups corresponding to occupation codes must exits
    (db/fill/occus1 must have been executed).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-07 09:05:28+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\app\Config;
use tiglib\patterns\Command;
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
            [$execReport, $execFile, $execN] = occu::execute([$slug, $file, 'full']);
            if($execN == 0){
                echo $execReport;
                continue;
            }
            // uses the fact that groups are named using the occupation slug (see command db/fill/occus1).
            $g = Group::createFromSlug($slug); // DB
// HERE not correct - the group shouldn't store the export
// suppress, use g5\Config when refactoring
            $download = str_replace(Config::$data['dirs']['output'] . DS, '', $execFile);
            $g->data['download'] = $download;
            $g->data['n'] = $execN;
            $g->update(updateMembers:false); // DB
            echo $execReport;
        }
        
        return $report;
    }
    
} // end class
