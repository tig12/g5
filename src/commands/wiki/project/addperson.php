<?php
/********************************************************************************
    Relates a person to a wiki project in database
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-05-28 20:36:08+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\project;

use g5\model\wiki\Wikiproject;
use tiglib\patterns\Command;

class addperson implements Command {
    
    /** 
        @param  $params Array containing 2 elements:
                    - the slug of the project to add ; ex: french-math
                      This slug must exist in database
                    - the slug of the person to add
        @return String report
    **/
    public static function execute($params=[]): string{
        $msg = "Associate a person to a wikiproject. Usage: 
        php run-g5.php wiki project addperson <project slug> <person slug>
        php run-g5.php wiki project addperson french-math weil-andre-1906-05-06
        ";
die("\nNOT IMPLEMENTED\ndie here " . __FILE__ . ' - line ' . __LINE__ . "\n");
        if(count($params) != 2){
            return "INVALID USAGE: This commands needs one parameter: the slug of the project to add\n";
        }
        $slug = $params[0];
        try{
            Wikiproject::insertFromSlug($slug);
        }
        catch(\Exception $e){
            return $e->getMessage() . "\n";
        }
        $report =  "--- wiki project add $slug ---\n";
        $report .= "Added project $slug in database\n";
        return $report;
    }
    
} // end class    
