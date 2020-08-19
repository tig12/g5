<?php
/********************************************************************************
    Loads A files in database files in data/tmp/cura
    
    @license    GPL
    @history    2020-08-19 05:23:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\A;

use g5\patterns\Command;
use g5\DB5;
use g5\commands\cura\Cura;

class tmp2db implements Command {
    
    // *****************************************
    // Implementation of Command
    /**
        @param $params  Empty array
        @param  $params Array containing 2 elements :
                        - a string identifying what is processed (ex : 'A1')
                        - "tmp2db" (useless here)
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[2] . "\n";
        }
        $datafile = $params[0];
        $report = "--- tmp2db ---\n";
        
        $lines = Cura::loadTmpFile($datafile);
        foreach($lines as $line){
echo "\n<pre>"; print_r($line); echo "</pre>\n"; exit;
        }
        
        return $report;
    }
        
}// end class    

