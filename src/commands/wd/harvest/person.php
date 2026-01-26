<?php
/********************************************************************************
    
    Retreieve on local machine the wikidata persons that will be matched to g5 persons.
    
    Input directory is data/db/wikidata/person (versioned with the code)
    Output directory is data/tmp/wikidata/person (unversioned)
    
    This command can be executed several times, persons already downloaded won't be downloaded agai.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-22 16:29:23+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\wd\harvest;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\wd\Wikidata;
use tiglib\misc\dosleep;

class person implements Command {
    
    /** 
        Query used to retrieve a wikidata person locally.
        {{wd_id}} is the placeholder to replace with the wd id of the entity
    **/
    const string QUERY = <<<QUERY
SELECT ?property ?propertyLabel ?value ?valueLabel WHERE {
  wd:{{wd_id}} ?prop ?value .
  ?property wikibase:directClaim ?prop .
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en".
  }
}
QUERY;
    
    /**
        Computes the directory where a wikidata person json file is stored, relative to Wikidata::INPUT_DIR.
        @param  $slug The slug of the person to store ; ex: galois-evariste-1811-10-25
        @return The relative directory path ; person/1811/10/25
        @throws Exception if the slug is incoherent.
        @see    g5\model\wiki\Wiki::slug2dir() has a similar logic
    **/
    public static function outputDir(string $slug): string {
        $p = '/(.*?)\-(\d+)\-(\d{2})\-(\d{2})/';
        preg_match($p, $slug, $m);
        if(count($m) != 5){
            throw new \Exception("Invalid slug: " . $slug);
        }
        $path = [
            Wikidata::TMP_DIR,
            'person',
            $m[2],
            $m[3],
            $m[4],
        ];
        return implode(DS, $path);
    }
    
    /**
        Computes the the filename of a wikidata person json file.
        @param  $slug The slug of the person to stores ; ex: galois-evariste-1811-10-25
        @return The json filename ; ex: galois-evariste-1811-10-25--Q7091.json
        @see    g5\model\wiki\Wiki::slug2dir() has a similar logic
    **/
    public static function outputFilename(string $g5_slug, string $wd_id): string {
        return $g5_slug . '--' . $wd_id . '.json';
    }
    
    // ****************** Main function, implementation of command ******************
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        
        $inputDir = Wikidata::INPUT_DIR . DS . 'person';
        $inputFiles = glob($inputDir . DS . '*.zip');
        
        foreach($inputFiles as $zipfile){
            $zip = new \ZipArchive;
            $zip->open($zipfile);
            $lines = explode("\n", $zip->getFromName(basename($zipfile, '.zip')));
            $N = count($lines);
            for($i=1; $i < $N; $i++){
                [$slug, $wd_id] = explode(';', $lines[$i]);
                $destDir = self::outputDir($slug, $wd_id);
                $destFile = $destDir . DS . self::outputFilename($slug, $wd_id);
                if(is_file($destFile)){
                    // file already retrieved
                    continue;
                }
                if(!is_dir($destDir)){
                    mkdir($destDir, 0755, true);
                    echo "Created directory $destDir\n";
                }
                $query = str_replace('{{wd_id}}', $wd_id, self::QUERY);
                [$respCode, $result] = Wikidata::query($query);
                if($respCode != 200){
                    echo "--- BAD RESPONSE CODE FOR $slug $wd_id : $respCode\n";
                    continue;
                }
                file_put_contents($destFile, $result);
                echo "Stored $destFile\n";
                dosleep::execute(1.5);
            }
        }
    }
    

}// end class    
