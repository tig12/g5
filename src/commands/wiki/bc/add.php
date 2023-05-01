<?php
/********************************************************************************
    Adds or updates information contained in a file BC.yml.
    Can concern a person already present in database (update) or a person not present in the database (insert).
    If the person is already in database, can concern the addition or update of BC informations.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\G5;
use g5\model\wiki\Wiki;
use g5\model\wiki\BC;
use g5\model\wiki\Wikiproject;
use g5\model\wiki\Recent;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\model\Trust;
use g5\model\Stats;
use tiglib\patterns\Command;

class add implements Command {
    
    /** Error message put in a function because also used by update.php **/
    public static function getErrorMessage() {
        return "This commands needs 1 or 2 parameter:\n"
                . "- the slug of the person to add (required)\n"
                . "- optional parameters, which can be:\n"
             // . "    - 'action' - possible values: 'add', 'upd', 'del' ; default value: 'add'.\n"
                . "    - 'action' - possible values: 'add', 'upd' ; default value: 'add'.\n"
                . "    - 'rw' - possible values: 'read', 'write' ; default value: 'write'.\n"
                . "Examples:\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add\n"
                . "    php run-g5.php wiki bc add galois-evariste-1811-10-25 action=add,rw=read\n";
    }
    
    /**
        Adds or updates in database birth certificate information contained in a BC.yml file.
        (deletion of BC currently not handled)
        When optional parameter rw = 'write', a new line is added in actions.csv.
            This is used for the addition of a new act.
            Current command directly used with php run-g5.php wiki bc add <act slug>
        When optional parameter rw = 'read', actions.csv is not modified.
            This is used when re-executing the actions, when the database is re-generated from scratch.
            Current command indirectly used with php run-g5.php db init wiki
        See $msg for parameters documentation.
        @return String report
    **/
    public static function execute($params=[]): string{
        $msg = self::getErrorMessage();
        if(count($params) != 1 && count($params) != 2){
            return "INVALID USAGE\n$msg";
        }
        //
        $personSlug = $params[0];
        try{
            $bcFile = BC::filePath($personSlug);
        }
        catch(\Exception $e){
            return "INVALID SLUG: $personSlug - nothing was modified in the database\n";
        }
        //
        $actSlug = $params[0];
        $PARAM_ACTION = 'add';
        $PARAM_RW = 'write';
        if(isset($params[1])){
            $options = G5::parseOptionalParameters($params[1]);
            $possibles = ['action', 'rw'];
            foreach($options as $k => $v){
                if(!in_array($k, $possibles)){
                    return "INVALID OPTIONAL PARAMETER: '$k'\n$msg";
                }
                switch($k){
                	case 'action': 
                	    $possibles2 = [Wiki::ACTION_ADD, Wiki::ACTION_UPDATE];
                	    //$possibles2 = [Wiki::ACTION_ADD, Wiki::ACTION_UPDATE, Wiki::ACTION_DELETE];
                	    if(!in_array($v, $possibles2)){
                            return "INVALID OPTIONAL PARAMETER 'action': '$v'\n$msg";
                	    }
                	    $PARAM_ACTION = $v;
                	break;
                	case 'rw': 
                	    $possibles2 = ['read', 'write'];
                	    if(!in_array($v, $possibles2)){
                            return "INVALID OPTIONAL PARAMETER 'rw': '$v'\n$msg";
                	    }
                	    $PARAM_RW = $v;
                	break;
                }
            }
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
        // $action and $PARAM_ACTION have 2 different meanings:
        // - $action indicates if a person should be added or updated
        // - $PARAM_ACTION indicates if a BC should be added or updated or deleted
        //
        $action = 'update';
        if(is_null($p)){
            $action = 'insert';
            $p = new Person();
            $p->data['slug'] = $personSlug;
        }
        //
        if($PARAM_ACTION == Wiki::ACTION_ADD && isset($p->data['acts']['birth'])){
            return "ERROR: YOU TRY TO ADD A BIRTH CERTIFICATE ALREADY ASSOCIATED TO A PERSON\n"
                . "Use instead:\n"
                . "    php run-g5.php wiki bc update $actSlug\n";
        }
        if($PARAM_ACTION == Wiki::ACTION_UPDATE && !isset($p->data['acts']['birth'])){
            return "ERROR: YOU TRY TO UPDATE A BIRTH CERTIFICATE NOT ALREADY ASSOCIATED TO A PERSON\n"
                . "Use instead:\n"
                . "    php run-g5.php wiki bc add $actSlug\n";
        }
        //
        // for addHistory
        //
        $url = Wiki::BASE_URL . '/' . str_replace(DS, '/', Wiki::slug2dir($actSlug));
        $url = "See <a href=\"$url\">Birth certificate transcription</a>";
        
        // New person created
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
                self::addRecent($p->data['id'], $BC, $PARAM_ACTION); // DB
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
            
            // Existing person updated
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
        if($PARAM_RW == 'write'){
            Wiki::addAction([
                'what'   => 'bc',
                'action' => $PARAM_ACTION,
                'slug'   => $actSlug,
            ]);
        }
        return $report;
    }
    
    // ******************************************************
    /**
        Auxiliary of execute()
    **/
    private static function addRecent(int $personId, array &$BC, string $PARAM_ACTION): void {
        if(isset($BC['header']['history']) && count($BC['header']['history']) != 0){
            $date = substr(end($BC['header']['history'])['date'], 0, 19);
            //$description = end($BC['header']['history'])['action'];
        }
        else {
            $date = '';
            //$description = '';
        }
        if($PARAM_ACTION == Wiki::ACTION_ADD){
            $description = 'Addition of birth certificate';
        }
        else if($PARAM_ACTION == Wiki::ACTION_UPDATE){
            $description = 'Update birth certificate';
        }
        // else if($PARAM_ACTION == Wiki::ACTION_DELETE){
        //     $description = 'Delete birth certificate';
        // }
        Recent::add(
            $personId,
            $date,
            $description,
        ); // DB
    }
    
    
} // end class    
