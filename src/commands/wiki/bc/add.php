<?php
/********************************************************************************
    Adds or updates information contained in a file BC.yml.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    If the person is already in database, can concern the addition or update of BC informations.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\model\wiki\Wiki;
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
        @param  $params Array containing 1 or 2 elements:
                        - the slug of the person to add (required)\n"
                        - an optional parameter, which can be 'add', 'upd' ; default value: 'add'
                        This parameter indicates if the act should be updated or deleted
        @return String report
    **/
    public static function execute($params=[]): string{
        $msg = "This commands needs 1 or 2 parameter:\n"
                . "- the slug of the person to add (required)\n"
                . "- an optional parameter, which can be 'add', 'upd' ; default value: 'add'.\n"
                . "This parameter indicates if the act should be updated or deleted\n";
        if(count($params) != 1 && count($params) != 2){
            return "INVALID USAGE\n$msg";
        }
        //
        $actSlug = $params[0];
        $param_action = 'add';
        if(count($params) == 2){
            $param_action = $params[1];
            if(!in_array($param_action, [Wiki::ACTION_ADD, Wiki::ACTION_UPDATE])){
                return "INVALID OPTIONAL PARAMETER 'action': '$v'\n$msg";
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
        if($param_action == Wiki::ACTION_ADD && isset($p->data['acts']['birth'])){
            return "ERROR: you try to add a birth certificate already associated to a person\n"
                . "Use instead: php run-g5.php wiki bc update $actSlug\n";
        }
        if($param_action == Wiki::ACTION_UPDATE && !isset($p->data['acts']['birth'])){
            return "ERROR: you try to update a birth certificate not already associated to a person\n"
                . "Use instead: php run-g5.php wiki bc add $actSlug\n";
        }
         
        $url = Wiki::BASE_URL . '/' . str_replace(DS, '/', Wiki::slug2dir($actSlug));

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
                    rawdata: ['url' => $url],
                );
                $p->update(); // DB - update() needed for occus and history
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
                //
                // search
                //
//        	    Search::updatePerson($p_orig, $p);          // TODO implement
                //
                if(isset($BC['extras']['occus'])){
                    $p->addOccus($BC['extras']['occus']);
                    $occus_orig = $p_orig->data['occus'];
                    $occus_new = $p->data['occus'];
                    // sort to be sure that array comparison works
                    sort($occus_orig);
                    sort($occus_new);
                    if($occus_new != $occus_orig){
                        $added = array_diff($occus_new, $occus_orig);
                        $removed = array_diff($occus_orig, $occus_new);
                        foreach($added as $occu){
                            $g = Group::createFromSlug($occu);
                            Group::storePersonInGroup($p->data['id'], $g->data['slug']); // DB
                        }
                        foreach($removed as $occu){
                            $g = Group::createFromSlug($occu);
                            Group::removePersonFromGroup($p->data['id'], $g->data['slug']); // DB
                        }
                    }
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
                    rawdata: ['url' => $url],
                );
                $p->update(); // DB
                $report .= "Updated $actSlug\n";
        	break;
        }
        Wiki::addAction('bc', $param_action, $actSlug);
        return $report;
    }
    
} // end class    
