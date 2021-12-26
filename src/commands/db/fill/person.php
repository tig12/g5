<?php
/******************************************************************************
    
    Updates or inserts persons in the database from a yaml file (in data/db/person).
    Each yaml file must contain an array.
    Each element of this array must contain the fields of a person, with a key ACTION.
    The value of key ACTION can be 'update' or 'delete'.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-12 14:28:11+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use tiglib\patterns\Command;
use g5\app\Config;
use g5\model\Person as ModelPerson;
use g5\model\Group as ModelGroup;

class person implements Command {
    
    private static $yamlFile = '';
    private static $nUpdate = 0;
    private static $nInsert = 0;
    
    /** 
        @param  $params Array containing one element:
                    path to the yaml file containing the tweaks, relative to data/db/tweak.
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "USELESS PARAMETER {$params[1]}\n";
        }
        if(count($params) != 1){
            return "MISSING PARAMETER: this command needs the path to the file containing the tweaks, relative to data/db/person.\n";
        }
        self::$yamlFile = $params[0];
        $yaml = @yaml_parse_file(Config::$data['dirs']['db'] . DS . 'person' . DS . self::$yamlFile);
        if($yaml === false){
            return 'FILE DOES NOT EXIST OR IS NOT CORRECTLY FORMATTED: ' . self::$yamlFile . "\n";
        }
        
        $report = "--- db fill tweak " . self::$yamlFile . " ---\n";
        
        foreach($yaml as $tweak){
            if(!isset($tweak['ADMIN']['ACTION'])){
                return "ERROR: every person must contain an ADMIN field 'ACTION'"
                    . " - concerned person:\n" . print_r($tweak, true) . "\n";
            }
            if(!in_array($tweak['ADMIN']['ACTION'], ['insert', 'update'])){
                return "ERROR: value of ADMIN field 'ACTION' can be 'insert' or 'update'"
                    . " - concerned person:\n" . print_r($tweak, true) . "\n";
            }
            
            // Remove ADMIN fields from tweak because tweak is used to fill the person
            // and ADMIN fields must not be stored in person.
            $ADMIN = $tweak['ADMIN'];
            unset($tweak['ADMIN']);
            //
            // HERE call insert() or update()
            //
            [$personId, $msg] = $ADMIN['ACTION'] == 'insert'
                ? self::insert($tweak)
                : self::update($tweak);
            if($msg != ''){
                return $msg;
            }
            //
            // Add person in groups
            // (a similar thing is already done in insert() or update() for occupations)
            //
            if(isset($ADMIN['ADD-IN-GROUPS'])){
                foreach($ADMIN['ADD-IN-GROUPS'] as $groupSlug){
                    try{
                        ModelGroup::storePersonInGroup($personId, $groupSlug);
                    }
                    catch(\Exception $e){
                        return $e->getMessage() . "\n";
                    }
                }
            }
        }
        if(self::$nUpdate != 0){
            $report .= 'Updated ' . self::$nUpdate . " persons in DB from {$params[0]}\n";
        }
        if(self::$nInsert != 0){
            $report .= 'Inserted ' . self::$nInsert . " persons in DB from {$params[0]}\n";
        }
        return $report;
    }
    
    // ******************************************************
    /**
        Updates a person already existing in db
        @return Array containing 2 elements: 
                - The id of the updated person ; -1 if update failed.
                - An error message ; empty string if no error.
    **/
    private static function update($tweak) {
        if(!isset($tweak['ids-in-sources'])){
            $msg = "TWEAK ERROR: every tweak must contain a field 'ids-in-sources'"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            return [-1, $msg];
        }
        if(count($tweak['ids-in-sources']) < 1){
            $msg = "TWEAK ERROR: field 'ids-in-sources' must contain at least one element"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            return [-1, $msg];
        }
                
        // Always use the first element of ids-in-sources to find the person
        $source = array_keys($tweak['ids-in-sources'])[0];
        $sourceId = $tweak['ids-in-sources'][$source];
        $p = ModelPerson::getBySourceId($source, $sourceId); // DB
        if(is_null($p)){
            $msg = "TWEAK ERROR: Person doesn't exist in database - source = $source, id = $sourceId"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            return [-1, $msg];
        }
        
        // OK, possible to update
        $new = $tweak;      // to update the person
        
        // fields that need precautions (arrays)
        // for these fields, the content of tweak is considered as a complement, not a replacement
        if(isset($tweak['occus'])){
            $p->addOccus($tweak['occus']);
            unset($new['occus']);
        }
        if(isset($tweak['name']['alter'])){
            $p->addAlternativeNames($tweak['name']['alter']);
            unset($new['name']['alter']);
        }
        if(isset($tweak['acts'])){
            $p->addActs($tweak['acts']);
            unset($new['acts']);
        }
        if(isset($tweak['notes'])){
            $p->addNotes($tweak['notes']);
            unset($new['notes']);
        }
        if(isset($tweak['issues'])){
            foreach($tweak['issues'] as $issue){
                $p->addIssue($issue);
            }
            unset($new['issues']);
        }
        
        $p->updateFields($new);
        // Recompute slug in case update changed name or birth date.
        $p->computeSlug();
        
        $p->addHistory(
            command: 'db fill person ' . self::$yamlFile,
            sourceSlug: self::$yamlFile, // not a real source slug
            rawdata: $tweak,
            newdata: $tweak,
        );
        
        $p->update(); // DB
        
        // Occupations must be handled separately because not done by $p->update()
        if(isset($tweak['occus'])){
            foreach($tweak['occus'] as $groupSlug){
                try{
                    ModelGroup::storePersonInGroup($p->data['id'], $groupSlug);
                }
                catch(\Exception $e){
                    // silently ignored, it means that the update contains occu already associated to person in db
                    return [$p->data['id'], ''];
                    // $msg = $e->getMessage() . "\nPerson ids-in-sources: " . print_r($tweak['ids-in-sources'], true);
                    // return [$p->data['id'], $msg];
                }
            }
        }
        
        self::$nUpdate++;
        return [$p->data['id'], ''];
    }
    
    // ******************************************************
    /**
        Inserts a new person in db
        @return Array containing 2 elements: 
                - The id of the inserted person ; -1 if insert failed
                - An error message ; empty string if no error.
    **/
    private static function insert($tweak) {
        $p = new ModelPerson();
        $p->updateFields($tweak);
        $p->computeSlug();
        $p->addHistory(
            command: 'db fill person ' . self::$yamlFile,
            sourceSlug: self::$yamlFile, // not a real source slug
            newdata: $tweak,
            rawdata: $tweak
        );
        try{
            $id = $p->insert(); // DB
            
            // Occupations must be handled separately because not done by $p->insert()
            if(isset($tweak['occus'])){
                foreach($tweak['occus'] as $groupSlug){
                    try{
                        ModelGroup::storePersonInGroup($id, $groupSlug);
                    }
                    catch(\Exception $e){
                        return [$p->data['id'], $e->getMessage() . "\n"];
                    }
                }
            }
            self::$nInsert++;
            return [$id, ''];
        }
        catch(\Exception $e){
            return [-1, $e->getMessage() . "\n"];
        }
    }
    
} // end class
