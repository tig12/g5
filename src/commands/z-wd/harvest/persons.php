<?php
/********************************************************************************
    
    From person ids stored in local machine, downloads persons' full data
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-10-31 17:17:16+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\wd\harvest;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\wd\Wikidata;
//use tiglib\arrays\csvAssociative;
use tiglib\strings\slugify;
use tiglib\misc\dosleep;

class persons implements Command {
    
    
    // *****************************************
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "USELESS PARAMETER : '{$params[0]}'. This command must be called without parameter.\n";
        }
        
        $baseDestDir = Wikidata::getRawPersonBaseDir();
        if(!is_dir($baseDestDir)){
            echo "Created $baseDestDir\n";
            mkdir($baseDestDir);
        }
        
        $p = '#http://www.wikidata.org/entity/(Q\d+)#';
        $baseUrl = 'https://www.wikidata.org/wiki/Special:EntityData/';
        
        $glob = glob(Wikidata::getRawPersonListBaseDir() . DS . '*' . DS . '*.json');
        foreach($glob as $infile){
            echo "=== Processing $infile ===\n";
            $raw = file_get_contents($infile);
            preg_match_all($p, $raw, $m);
            foreach($m[1] as $id){
                $url = $baseUrl . $id . '.json';
//                $json = file_get_contents($url);

                $raw = file_get_contents('/home/thierry/dev/astrostats/gauquelin5/data/1-raw/z.wikidata.org-BCK/Q41390-godel.json');
                $id = 'Q41390';
                $json = json_decode($raw, true);
                if(isset($json['entities'][$id]['labels']['en']['value'])){
                    $slug = slugify::compute($json['entities'][$id]['labels']['en']['value']);
                }
                else{
                    $slug = 'no-name';
                }                                                                          
                if(isset($json['entities'][$id]['claims']['P569'][0]['mainsnak']['datavalue']['value']['time'])){
                    $bdate = substr($json['entities'][$id]['claims']['P569'][0]['mainsnak']['datavalue']['value']['time'], 1, 10);
                }
                else{
                    $bdate = false;
                }
                $destDir = Wikidata::computeRawPersonDir($id, $slug, $bdate);
                if(!is_dir($destDir)){
                    mkdir($destDir, 0777, true);
                }
                $destFile = Wikidata::computeRawPersonFilename($id, $slug, $bdate);
                file_put_contents($destFile, $json);
                echo "Stored $destFile\n";
            }
        }
        
        return '';
    }
    

}// end class    
