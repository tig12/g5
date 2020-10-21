<?php
/******************************************************************************
    
    Builds occupation codes from wikidata
    
    DRAFT CODE - UNFINISHED, NOT USED YET
    
    @license    GPL
    @history    2020-08-27 00:29:07+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\build;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;

class occu implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
    **/
    public static function execute($params=[]): string {
        $dir_wd = '/home/thierry/dev/astrostats/z.gauquelin5-BCK/data/1-raw/wikidata.org/profession-lists';
        $infiles = glob($dir_wd . DS . '*.json');
        foreach($infiles as $infile){
            echo "\n$infile\n";
            $json = json_decode(file_get_contents($infile));
            foreach($json->results->bindings as $elt){
                $url = $elt->occupation->value;
                $wdid = str_replace('http://www.wikidata.org/entity/', '', $url);
                $name = $elt->occupationLabel->value;
                if(preg_match('/Q\d+/', $name)){
                    continue;
                }
                echo "$wdid $name\n";
            }
        }
        
        $report = '';
        return $report;
    }
    
    
}// end class
