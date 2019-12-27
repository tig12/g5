<?php
/********************************************************************************
    
    Retrieves the lists of occupations subclasses of Wikidata::OCCUPATION_SEEDS

    @license    GPL
    @history    2019-11-01 22:16:48+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\wd\harvest;

use g5\Config;
use g5\patterns\Command;
use g5\commands\wd\Wikidata;
use tiglib\misc\dosleep;

class occus implements Command{
    
    // *****************************************
    /** 
        @param $params array with one element :
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $destDir = Wikidata::getRawProfessionListDir();
        if(!is_dir($destDir)){
            echo "Created $destDir\n";
            mkdir($destDir);
        }
        
        $q1 = 'SELECT ?occupation ?occupationLabel WHERE{ ?occupation wdt:P279 wd:';
        $q2 = '. SERVICE wikibase:label { bd:serviceParam wikibase:language "en" } } ORDER BY (?occupationLabel)';
        
        foreach(Wikidata::OCCUPATION_SEEDS as $professionCode => $professionSlug){
            
            echo "\n";
            
            $destFile = $destDir . DS . $professionSlug . '.json';
            if(is_file($destFile)){
                echo "SKIPPING $destFile (already exists)\n";
                continue;                                                                                              
            }
            
            $query = $q1 . $professionCode . $q2;
            echo "Downloading $professionCode $professionSlug ...\n";
            [$respCode, $result] = Wikidata::query($query);
            if($respCode != 200){
                echo "DOWNLOAD ERROR - response code = $respCode\n";
                continue;
            }
            file_put_contents($destFile, $result);
            echo "$destFile saved\n";
            dosleep::execute(2);
        }
        return '';
    }
        
}// end class
