<?php
/******************************************************************************
    Merges
        - data/tmp/wikidata/all-professions.csv
        - data/build/occu/occu.yml
        - data/build/occu/sport-ertel-gauquelin.yml
    
    @license    GPL
    @history    2020-10-21 02:23:12+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\build;

use g5\patterns\Command;
use g5\Config;
use g5\model\DB5;
use g5\model\Occupation;
use g5\commands\wd\Wikidata;
use tiglib\arrays\csvAssociative;
use tiglib\strings\slugify;

class occu implements Command {
    
    /** Result of src/commands/wd/harvest/all-professions.sparql **/
    const WD_TMP_FILE = 'data/build/wikidata/all-professions.csv';
    
    // *****************************************
    /** 
        @param  $params empty array
    **/
    public static function execute($params=[]): string { // Command Implementation
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $occus = self::loadOccus();
        $er_gqs = yaml_parse_file(Occupation::getBuildDir() . DS . 'sport-ertel-gauquelin.yml');
        $wds = self::loadWD();
        
        $res = [];
        $nMatch = $nNomatch = 0;
//echo "\n"; print_r($wds['artist']); echo "\n";
//echo "\n"; print_r($occus['artist']); echo "\n";
        foreach($occus as $slug => $occu){
            if(isset($wds[$slug])){
                $nMatch++;
echo "MATCH {$wds[$slug]['id']} $slug\n";
            } else {
                $nNomatch++;
//echo "NO MATCH $slug\n";
            }
        }
        $report = '';
        $report .= "N match:    $nMatch\n";
        $report .= "N no match: $nNomatch\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Returns occupations in an assoc array
        slug => occupation
    **/
    public static function loadOccus(){
        $occus = yaml_parse_file(Occupation::getBuildDir() . DS . 'occu.yml');
        $res = [];
        foreach($occus as $occu){
            $slug = slugify::compute($occu['en']);
            $res[$slug] = $occu;
        }
        return $res;
    }
    
    // ******************************************************
    /**
        Returns wikidata occupations in an assoc array
        slug => [
            'id' => ...,
            'label' => ...,
        ]
    **/
    public static function loadWD(){
        $wd = csvAssociative::compute(Occupation::getBuildDir() . DS . 'athlete-subclasses.csv', ',');
        //$wd = csvAssociative::compute(Occupation::getBuildDir() . DS . 'profession-instances.csv', ',');
        $res = [];
        foreach($wd as $occu){
            if(preg_match(Wikidata::PATTERN_ID, $occu['entityLabel']) == 1){
                continue; // no label, only wd id
            }
            $slug = slugify::compute($occu['entityLabel']);
            $res[$slug] = [
                'id' => str_replace('http://www.wikidata.org/entity/', '', $occu['entity']),
                'label' => $occu['entityLabel'],
            ];
        }
        return $res;
    }
    
}// end class
