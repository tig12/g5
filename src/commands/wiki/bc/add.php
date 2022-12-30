<?php
/********************************************************************************
    Adds the information contained in a file BC.yml in the database.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\commands\wiki\Wiki;
use g5\model\BC;
use g5\model\Person;
use g5\model\Source;
use g5\model\Trust;
use tiglib\patterns\Command;

class add implements Command {
    
    /** 
        @param  $params Array containing one element:
                    the slug of the person to add ; ex: galois-evariste-1811-10-25
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter\n";
        }
        
        $slug = $params[0];
        
        try{
            $bcFile = BC::filePath($slug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $slug - nothing was modified in the database\n";
        }
        if(!is_file($bcFile)){
            return "Impossible to add wiki information because file '$bcFile' is missing\n";
        }
        
        $yaml = yaml_parse_file($bcFile);
        
        $validation = BC::validate($yaml);
        if($validation != ''){
            return "INVALID YAML FILE: $bcFile"
                . "\n$validation"
                . "Information not included in the database\n";
        }
        
        $source = new Source();
        $source->data['type'] = BC::SOURCE_TYPE;
        $source->data['name'] = BC::SOURCE_LABEL;
        $source->data['details'] = $yaml['source'];
//echo "\n"; print_r($source); echo "\n";        
//exit;
        $p = Person::createFromSlug($slug);
        if(is_null($p)){
            $p = new Person();
        }
        // The informations coming from a BC are considered as superior to all other sources.
        // updateFields() is the also called for a person already in database
        $p->updateFields($yaml['person']);
        $p->data['trust'] = Trust::BC;
        if(isset($yaml['extras']['occupations'])){
            $p->addOccus($yaml['extras']['occupations']);
        }
        $p->data['slug'] = $slug;
        $p->addHistory(
            command: 'wiki bc add ' . $p->data['slug'],
            sourceSlug: BC::SOURCE_SLUG,
            newdata: $yaml['person'],
            rawdata: $yaml['person']
        );
echo "\n"; print_r($p); echo "\n";
//echo "$bcFile\n";
//echo "\n"; print_r($yaml); echo "\n";
        
        $report =  "--- wiki add one ---\n";
        
        return $report;
    }
    
} // end class    
