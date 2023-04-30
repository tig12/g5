<?php
/********************************************************************************
    Adds or updates information contained in a file BC.yml.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    If the person is already in database, can concern the addition or update of BC informations.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\commands\wiki\Wiki;
use g5\model\wiki\BC;
use g5\model\wiki\Wikiproject;
use g5\model\wiki\Recent;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\model\Trust;
use g5\model\Stats;
use g5\G5;
use tiglib\patterns\Command;

class add implements Command {
    
    /** 
        Ex: php run-g5.php wiki bc add galois-evariste-1811-10-25
        Ex: php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add
        Ex: php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add,rw=read
        @param  $params Array containing 1 or 2 elements:
                        - the slug of the person to add (required)
                        - optional parameters, which can be:
                            - 'action' - possible values: 'add', 'upd', 'del' ; default value: 'add'.
                            - 'rw' - possible values: 'read', 'write' ; default value: 'write'.
        @return String report
    **/
    public static function execute($params=[]): string{
        $msg = "This commands needs 1 or 2 parameter:\n"
                . "- the slug of the person to add (required)\n"
                . "- optional parameters, which can be:\n"
                . "    - 'action' - possible values: 'add', 'upd', 'del' ; default value: 'add'.\n"
                . "    - 'rw' - possible values: 'read', 'write' ; default value: 'write'.\n"
                . "Examples:\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add,rw=read\n";
        if(count($params) != 1 && count($params) != 2){
            return "INVALID USAGE\n$msg";
        }
        //
        $actSlug = $params[0];
        $ACTION = 'add';
        $RW = 'write';
        if(isset($params[1])){
            $options = G5::parseOptionalParameters($params[1]);
            $possibles = ['action', 'rw'];
            foreach($options as $k => $v){
                if(!in_array($k, $possibles)){
                    return "INVALID OPTIONAL PARAMETER: '$k'\n$msg";
                }
                switch($k){
                	case 'action': 
                	    $possibles2 = ['add', 'upd', 'del'];
                	    if(!in_array($v, $possibles2)){
                            return "INVALID OPTIONAL PARAMETER 'action': '$v'\n$msg";
                	    }
                	    $ACTION = $v;
                	break;
                	case 'rw': 
                	    $possibles2 = ['read', 'write'];
                	    if(!in_array($v, $possibles2)){
                            return "INVALID OPTIONAL PARAMETER 'rw': '$v'\n$msg";
                	    }
                	    $RW = $v;
                	break;
                }
            }
        }
        //
        $bcFile = BC::filePath($actSlug);
        if(!is_file($bcFile)){
            return "ERROR: Impossible to add BC information because file '$bcFile' is missing\n";
        }
        //
        $BC = BC::createFromYamlFile($bcFile);
        //
        $validation = BC::validate($BC);
        if($validation != ''){
            return "INVALID YAML FILE: $bcFile"
                . "\n$validation"
                . "Information not included in the database\n";
        }
        //
        $commandName = "wiki bc add $actSlug";
        $report =  "--- $commandName ---\n";
        //
        // Person slug
        // Logic :
        //     1 - try $BC['opengauquelin']['old-slug']
        //     2 - person slug is the act slug
        //
        if(isset($BC['opengauquelin']['old-slug']) && $BC['opengauquelin']['old-slug'] != ''){
            $personSlug = $BC['opengauquelin']['old-slug'];
        }
        else{
            $personSlug = $actSlug;
        }
        //
        $p = Person::createFromSlug($personSlug);
        //
        $action = 'update';
        if(is_null($p)){
            $action = 'insert';
            $p = new Person();
            $p->data['slug'] = $personSlug;
        }
        //
        switch($action){
        	case 'insert':
        	    $p->addBC($BC);
        	    // insert() needed now to have the person id
                $p->insert(); // DB
        	    //
        	    // stats
        	    //
                Stats::addPerson($p); // DB
                //
                // search
                //
//        	    Search::addPerson($p);          // TODO implement
                //
                // wiki projects
                //
                if(isset($BC['opengauquelin']['projects'])){
        	        foreach($BC['opengauquelin']['projects'] as $projectSlug){
                        Wikiproject::addActToProject($projectSlug, $p); // DB
                    }
                }
                //
                // recent acts - table wikirecent
                //
                if(isset($BC['header']['history']) && count($BC['header']['history']) != 0){
                    $dateRecent = substr(end($BC['header']['history'])['date'], 0, 19);
                    $descriptionRecent = end($BC['header']['history'])['action'];
                }
                else {
                    $dateRecent = '';
                    $descriptionRecent = '';
                }
                Recent::add(
                    $p->data['id'],
                    $dateRecent,
                    "Addition of birth certificate",
                ); // DB
                //
                // Occupations
                //
                if(count($p->data['occus']) != 0){
                    foreach($p->data['occus'] as $occu){
                        $g = Group::createFromSlug($occu);
                        Group::storePersonInGroup($p->data['id'], $g->data['slug']); // DB
                    }
                }
        	    //
        	    // TODO modify exports
        	    //
                //
                $p->addHistory(
                    command: $commandName,
                    sourceSlug: 'Birth certificate',
                    newdata: array_merge_recursive($BC['transcription'], $BC['extras']),
                    rawdata: [],
                );
        	    // update() needed for occus and history
                $p->update(); // DB
                $report .= "Inserted $actSlug\n";
            break;
            
            case 'update':
                $p_orig = clone $p;
                //
        	    $p->addBC($BC);
        	    //
        	    // stats
        	    //
        	    Stats::updatePerson($p_orig, $p);
exit;
                //
                // search
                //
//        	    Search::updatePerson($p_orig, $p);          // TODO implement
                //
                if(isset($BC['extras']['occus'])){
                    $p->addOccus($BC['extras']['occus']);
                }
                //
                // wiki projects
                //
                if(isset($BC['opengauquelin']['projects'])){
        	        foreach($BC['opengauquelin']['projects'] as $projectSlug){
        	            // Wikiproject::addActToProject() adds to the project only if the person is not already associated 
                        Wikiproject::addActToProject($projectSlug, $p);
                    }
                }
                $p->addHistory(
                    command: $commandName,
                    sourceSlug: 'Birth certificate',
                    newdata: array_merge_recursive($BC['transcription'], $BC['extras']),
                    rawdata: [],
                );
                $p->update(); // DB
                $report .= "Updated $actSlug\n";
        	break;
        }
        return $report;
    }
    
} // end class    
