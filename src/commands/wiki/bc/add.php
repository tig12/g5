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
use g5\model\wiki\Project;
use g5\model\wiki\Recent;
use g5\model\Act;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\model\Trust;
use g5\model\Stats;
use tiglib\patterns\Command;

class add implements Command {
    
    /** 
        @param  $params Array containing one element: the slug of the person to add
                        ex: wiki bc add galois-evariste-1811-10-25
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 1){
            return "INVALID USAGE This commands needs one parameter\n";
        }
        
        $personSlug = $params[0];
        
        try{
            $bcFile = BC::filePath($personSlug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $personSlug - nothing was modified in the database\n";
        }
        if(!is_file($bcFile)){
            return "Impossible to add wiki information because file '$bcFile' is missing\n";
        }
        
        $BC = yaml_parse_file($bcFile);
        
        $validation = BC::validate($BC);
        if($validation != ''){
            return "INVALID YAML FILE: $bcFile"
                . "\n$validation"
                . "Information not included in the database\n";
        }
        
        $report =  "--- wiki bc add $personSlug ---\n";
        
        $source = new Source();
        $source->data['type'] = BC::SOURCE_TYPE;
        $source->data['name'] = BC::SOURCE_LABEL;
        $source->data['details'] = $BC['source'];
        
        $p = Person::createFromSlug($personSlug);
        
        $action = 'update';
        if(is_null($p)){
            $action = 'insert';
            $p = new Person();
            $p->data['slug'] =  $personSlug;
        }
        
        Act::addActToPerson($p, Act::BIRTH, $personSlug);
        
        switch($action){
        	case 'insert':
        	    //
                $p->insert(); // DB - can throw an exception
        	    //
                Stats::addPerson($p);
                //
//        	    Search::addPerson($p);          // TODO implement
        	    //
                if(isset($BC['opengauquelin']['projects'])){
        	        foreach($BC['opengauquelin']['projects'] as $projectSlug){
                        Project::addPersonToProject($projectSlug, $p);
                    }
                }
                //
                if(isset($BC['header']['history']) && count($BC['header']['history']) != 0){
                    $dateRecent = substr(end($BC['header']['history'])['date'], 0, 19);
                }
                else {
                    $dateRecent = '';
                }
                Recent::add(
                    $p->data['id'],
                    $dateRecent,
                    "Addition of birth certificate",
                );
                //
                // TODO This code should be moved to wiki/fix/add
                if(count($p->data['occus']) != 0){
                    foreach($p->data['occus'] as $occu){
                        $g = Group::createFromSlug($occu);
                        Group::storePersonInGroup($p->data['id'], $g->data['slug']);
                    }
                }
                $report .= "Inserted $personSlug\n";
            break;
            case 'update':
                if(isset($BC['extras']['occus'])){
                    $p->addOccus($BC['extras']['occus']);
                }
                $p->update(); // DB - can throw an exception
        	    // Stats::updatePerson($p);        // TODO implement (check if notime has changed from true to false)
        	    // Search::updatePerson($p);       // TODO implement
                if(isset($BC['opengauquelin']['projects'])){
        	        foreach($BC['opengauquelin']['projects'] as $projectSlug){
        	            // Project::addPersonToProject() adds to the project only if the person is not already associated 
                        Project::addPersonToProject($projectSlug, $p);
                    }
                }
                $report .= "Updated $personSlug\n";
        	break;
        }
        
        return $report;
    }
    
} // end class    
