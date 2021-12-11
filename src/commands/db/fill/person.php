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
            if(!isset($tweak['ACTION'])){
                return "ERROR: every person must contain a field 'ACTION'"
                    . " - concerned person:\n" . print_r($tweak, true) . "\n";
            }
            if(!in_array($tweak['ACTION'], ['insert', 'update'])){
                return "ERROR: value of field 'ACTION' can be 'insert' or 'update'"
                    . " - concerned person:\n" . print_r($tweak, true) . "\n";
            }
            
            // HERE call inset() or update()
            $msg = $tweak['ACTION'] == 'insert' ? self::insert($tweak) : self::update($tweak);
            
            if($msg != ''){
                return $msg;
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
    
    /**
        Updates a person already exiting in db
    **/
    private static function update($tweak) {
        if(!isset($tweak['ids-in-sources'])){
            return "TWEAK ERROR: every tweak must contain a field 'ids-in-sources'"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
        }
        if(count($tweak['ids-in-sources']) < 1){
            return "TWEAK ERROR: field 'ids-in-sources' must contain at least one element"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
        }
        
        unset($tweak['ACTION']);
        
        // Always use the first element of ids-in-sources to find the person
        $source = array_keys($tweak['ids-in-sources'])[0];
        $id = $tweak['ids-in-sources'][$source];
        $p = ModelPerson::getBySourceId($source, $id); // DB
        if(is_null($p)){
            return "TWEAK ERROR: Person doesn't exist in database - source = $source, id = $id"
                . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
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
        
        $p->updateFields($new);
        $p->computeSlug(); // HERE recompute slug if the update changes name or birth date
        
        $p->addHistory(
            command: 'db fill person ' . self::$yamlFile,
            sourceSlug: self::$yamlFile, // not a real source slug
            newdata: $new,
            rawdata: $tweak
        );
        
        $p->update(); // DB
        self::$nUpdate++;
        return '';
    }
    
    /**
        Inserts a new person in db
    **/
    private static function insert($tweak) {
        unset($tweak['ACTION']);
        $p = new ModelPerson();
        $p->updateFields($tweak);
        $p->computeSlug();
        $p->addHistory(
            command: 'db fill person ' . self::$yamlFile,
            sourceSlug: self::$yamlFile, // not a real source slug
            newdata: $tweak,
            rawdata: $tweak
        );
        $p->insert(); // DB
        self::$nInsert++;
//echo "\n"; print_r($p->data); echo "\n";
        return '';
    }
    
} // end class
