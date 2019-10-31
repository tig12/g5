<?php
/********************************************************************************
    
    From data/1-raw/wikidata.org/all-professions.csv retrieves the lists of persons
    associated to each profession code.

    @license    GPL
    @history    2019-05-16 12:16:35+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\wd\harvest;

use g5\Config;
use g5\patterns\Command;
use g5\transform\wd\Wikidata;
use tiglib\arrays\csvAssociative;
use tiglib\strings\slugify;
use tiglib\misc\dosleep;

class lists implements Command{
    
    // *****************************************
    /** 
        @param $params array with one element :
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $filename = Config::$data['dirs']['1-wd-raw'] . DS . 'all-professions.csv';
        if(!is_file($filename)){
            return "File does not exist : $filename\n"
                 . "You must create it before executing this command - see doc, page about wikidata\n";
        }
        $csv = csvAssociative::compute($filename, Wikidata::RAW_CSV_SEP);
        
        $baseDir = Wikidata::getRawPersonListBaseDir();
        if(!is_dir($baseDir)){
            return "Directory does not exist : $baseDir\n"
                 . "You must create it before executing this command\n";
        }
        
        // Prepare sub-directories of data/1-raw/wikidata.org/person-lists
        for($i=97; $i < 123; $i++){
            //echo chr($i) . "\n";
            $dir = $baseDir . DS . chr($i);
            if(!is_dir($dir)){
                mkdir($dir);
            }
        }
        
        //$q1 = urlencode('SELECT DISTINCT ?person ?personLabel WHERE{ ?person ?P31 wd:');
        //$q2 = urlencode(' SERVICE wikibase:label {bd:serviceParam wikibase:language "en". }');
        $baseUrl = 'https://query.wikidata.org/sparql?format=json&query=';
        $q1 = 'SELECT%20DISTINCT%20%3Fperson%20%3FpersonLabel%0AWHERE%20%7B%0A%20%20%20%20%3Fperson%20%3FP31%20wd%3A';
        $q2 = '%3B%0A%20%20%20%20SERVICE%20wikibase%3Alabel%20%7B%20bd%3AserviceParam%20wikibase%3Alanguage%20%22en%22.%20%7D%0A%7D%0A';     
        foreach($csv as $line){
            
            echo "\n";
            
            $slug = slugify::compute($line['professionLabel']);
            $destFile = Wikidata::getRawPersonListDir($slug) . DS . $slug . '.json';
            if(is_file($destFile)){
                echo "SKIPPING $destFile (already exists)\n";
                continue;                                                                                              
            }
            
            $wdCode = substr($line['profession'], 31);
            $query = $q1 . $wdCode . $q2;
            $url = $baseUrl . $query;
            
            echo "Downloading {$line['professionLabel']} ...\n";
            
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL,  $url);
            curl_setopt( $ch, CURLOPT_POST, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch , CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded",
                "Accept: application/sparql-results+json"
            ]);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0');
            $result = curl_exec($ch);
            $respCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
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
