<?php
/********************************************************************************
    Adds the information contained in a file BC.yml in the database.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\commands\wiki\Wiki;
use g5\model\Person;
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
            $bcFile = Wiki::bcFile($slug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $slug - nothing was modified in the database\n";
        }
        if(!is_file($bcFile)){
            return "Impossible to add wiki information because file '$bcFile' is missing\n";
        }
        
        $yaml = yaml_parse_file($bcFile);
        
        $validation = Wiki::validateBC($yaml);
        if($validation != ''){
            return "INVALID YAML FILE: $bcFile"
                . "\n$validation"
                . "Information not included in the database\n";
        }
        
        $source = $yaml['source'];
        
        $p = Person::createFromSlug($slug);
        if(is_null($p)){
            $p = new Person();
            $p->updateFields($yaml['person']);
echo "\n"; print_r($p); echo "\n";
        }
//echo "$bcFile\n";
//echo "\n"; print_r($yaml); echo "\n";
exit;
        
        $report =  "--- wiki add one ---\n";
        
        return $report;
    }
    
} // end class    
