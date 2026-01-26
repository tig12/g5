<?php
/********************************************************************************
    
    Lists occupation codes of the wikidata persons in data/tmp/wikidata/person 
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-24 20:37:55+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\wd\look;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\wd\Wikidata;
use g5\commands\wd\Entity;
use g5\commands\wd\Property;

class occu implements Command {
    
    public const string OCCUPATION_URL = Entity::ENTITY_URL . '/' . Property::OCCUPATION;
    
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        
        // if(count($params) > 0){
            // return "USELESS PARAMETER : '{$params[0]}'. This command must be called without parameter.\n";
        // }
        
        $inputFiles = glob(implode(DS, [
            Wikidata::TMP_DIR,
            'person',
            '*',
            '*',
            '*',
            '*.json'
        ]));
        
        // associative array occu wd id => occu label
        $allOccus = [];
        
echo count($inputFiles) . "\n"; exit;
        foreach($inputFiles as $file){
            $wd_person = json_decode(file_get_contents($file), true);
            foreach($wd_person['results']['bindings'] as $binding){
                if($binding['property']['value'] == self::OCCUPATION_URL){
                    $id_occu = str_replace(Entity::ENTITY_URL . '/', '', $binding['value']['value']);
                    $label_occu = $binding['valueLabel']['value'];
                    $allOccus[$id_occu] = $label_occu;
                }
            }
        }
        asort($allOccus, SORT_STRING|SORT_FLAG_CASE);
        
        //$format = 'csv';
        $format = 'csv';
        if($format == 'csv'){
            echo "WDID;LABEL\n";
            foreach($allOccus as $id_occu => $label_occu){
                echo "$id_occu;$label_occu\n";
            }
        }
//        echo "\n"; print_r($allOccus); echo "\n";
        echo count($allOccus) . "\n";
    }
    

}// end class    
