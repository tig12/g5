<?php
/********************************************************************************
    
    Retrieves lists of person ids, using the lists in 1-wd-raw/profession-lists
    Resulting lists are stored in sub-directories of 1-wd-raw/person-lists

    @license    GPL
    @history    2019-11-02 07:47:25+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\wd\harvest;

use g5\Config;
use g5\patterns\Command;
use g5\commands\wd\Wikidata;
use tiglib\strings\slugify;
use tiglib\misc\dosleep;

class personLists implements Command{
    
    // *****************************************
    /** 
        @param $params array with one element :
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $baseDestDir = Wikidata::getRawPersonListBaseDir();
        if(!is_dir($baseDestDir)){
            echo "Created $baseDestDir\n";
            mkdir($baseDestDir);
        }
        
        $q1 = 'SELECT DISTINCT ?person ?personLabel WHERE { ?person ?P31 wd:';
        $q2 = '; SERVICE wikibase:label { bd:serviceParam wikibase:language "en". } }';
        
        $inputDir = Wikidata::getRawProfessionListDir();
        
        // loop on files of profession-lists/
        $inputFiles = glob($inputDir . DS . '*.json');
        foreach($inputFiles as $inputFile){
            
            echo "\n";
            
            $tmp = basename($inputFile, '.json');
            $destDir = $baseDestDir . DS . $tmp; // ex person-lists/athlet
            
            if(!is_dir($destDir)){
                echo "Create $destDir\n";
                mkdir($destDir);
            }
            
            $json = json_decode(file_get_contents($inputFile), true);
            // loop on precise profession codes
            foreach($json['results']['bindings'] as $item){
                $professionCode = substr($item['occupation']['value'], 31);
                $professionSlug = slugify::compute($item['occupationLabel']['value']);
                
                $destFile = $destDir . DS . $professionSlug . '.json';
                if(is_file($destFile)){
                    echo "SKIPPING $destFile (already exists)\n";
                    continue;                                                                                              
                }
                
                $query = $q1 . $professionCode . $q2;
                echo "Downloading person codes of $professionCode $professionSlug ...\n";
                [$respCode, $result] = Wikidata::query($query);
                if($respCode != 200){
                    echo "DOWNLOAD ERROR - response code = $respCode\n";
                    continue;
                }
                file_put_contents($destFile, $result);
                echo "$destFile saved\n";
                dosleep::execute(2);
            }
        }
        return '';
    }
        
}// end class
