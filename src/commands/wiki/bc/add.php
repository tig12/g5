<?php
/********************************************************************************
    Adds the information contained in a file BC.yml in the database.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\commands\wiki\Wiki;
use g5\model\wiki\BC;
use g5\model\Act;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\model\Trust;
use g5\model\Stats;
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
        
        $report =  "--- wiki bc add $slug ---\n";
        
        $source = new Source();
        $source->data['type'] = BC::SOURCE_TYPE;
        $source->data['name'] = BC::SOURCE_LABEL;
        $source->data['details'] = $yaml['source'];
        
        $p = Person::createFromSlug($slug);
        
        $action = 'update';
        if(is_null($p)){
            $action = 'insert';
            $p = new Person();
            $p->data['slug'] =  $slug;
        }
        
        Act::personAct($p, Act::BIRTH, $slug);
        
        switch($action){
        	case 'insert': 
//echo "\n<pre>"; print_r($p->data['occus']); echo "</pre>\n"; exit;
                $p->insert(); // can throw an exception
                
//        	    Stats::addPerson($p);
        	    
//        	    Search::addPerson($p);          // TODO implement
        	    
                if(count($p->data['occus']) != 0){
                    foreach($p->data['occus'] as $occu){
                        $g = Group::createFromSlug($occu);
                        Group::storePersonInGroup($p->data['id'], $g->data['slug']);
                    }
                }
                $report .= "Inserted $slug\n";
            break;
            case 'update':
//        	    $p->addOccus();         ////////////////////// EN COURS
                $p->update(); // can throw an exception
        	    // Stats::updatePerson($p);        // TODO implement (check if notime has changed from true to false)
        	    // Search::updatePerson($p);       // TODO implement
                $report .= "Updated $slug\n";
        	break;
        }
        
//echo "\n"; print_r($p); echo "\n";
//echo "$bcFile\n";
//echo "\n"; print_r($yaml); echo "\n";
        
        return $report;
    }
    
} // end class    
