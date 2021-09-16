<?php
/******************************************************************************
    
    Updates persons in the database from a tweak file (in data/db/tweak)
    
    @license    GPL
    @history    2021-08-12 14:28:11+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use tiglib\patterns\Command;
use g5\app\Config;
use g5\model\Tweak as ModelTweak;
use g5\model\Person;

class tweak implements Command {
    
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
            return "MISSING PARAMETER: this command needs the path to the file containing the tweaks\n";
        }
        $yamlfile = Config::$data['dirs']['db'] . DS . 'tweak' . DS . $params[0];
        $yaml = @yaml_parse_file($yamlfile);
        if($yaml === false){
            return "FILE DOES NOT EXIST: $yamlfile\n";
        }
        
        $report = "--- db fill tweak ---\n";
        
        foreach($yaml as $tweak){
            if(!isset($tweak['ids-in-sources'])){
                return "TWEAK ERROR: every tweak must contain a field 'ids-in-sources'"
                    . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            }
            if(count($tweak['ids-in-sources']) < 1){
                return "TWEAK ERROR: field 'ids-in-sources' must contain at least one element"
                    . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            }
            
            // Always use the first element of ids-in-sources to find the person
            $source = array_keys($tweak['ids-in-sources'])[0];
            $id = $tweak['ids-in-sources'][$source];
            $p = Person::getBySourceId($source, $id); // DB
            if(is_null($p)){
                return "TWEAK ERROR: Person doesn't exist in database - source = $source, id = $id"
                    . " - concerned tweak:\n" . print_r($tweak, true) . "\n";
            }
            
            // OK, possible to update
            $new = $tweak;      // to update the person
            unset($new[ModelTweak::BUILD_NOTES]);
            
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
            
            $p->updateFields($new);
            
            $p->addHistory(
                command: 'db fill tweak2db ' . $params[0],
                sourceSlug: $yamlfile, // not a real source slug
                newdata: $new,
                rawdata: $tweak
            );
            
            $p->update(); // DB
        }
        $report .= 'Updated ' . count($yaml) . " persons in DB from {$params[0]}\n";
        return $report;
    }
    
} // end class
