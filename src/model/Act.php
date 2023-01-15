<?php
/********************************************************************************
    Constants and utilities.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-03-22 21:09:32+01:00, Thierry Graff : Creation
    @history    2023-01-01 20:23:00+01:00, Thierry Graff : Refactor, limit to mimimum
********************************************************************************/
namespace g5\model;

use g5\app\Config;
use g5\model\Person;
use g5\model\Trust;
use g5\model\wiki\BC;

class Act {
    
    const BIRTH = 'birth';
    const DEATH = 'death';
    const MARIAGE = 'mariage';
    
    const SOURCE_SLUG = 'act';
    
    /**
        Returns the directory where the acts of a given sort are stored.
        Uses the structure of data/acts:
        /path/to/acts/
            ├── birth
            ├── death
            └── mariage
        @param  $key    Can be 'birth', 'death', 'mariage'
    **/
    public static function computeFile(string $actKey, string $actSlug): string {
        switch($actKey){
        	case self::BIRTH:  return BC::filePath($actSlug); break;
        	default:           throw new \Exception("-- NOT IMPLEMENTED -- Act::computeDir($key)");
        }
    }
    
    /**
        Delegate for Person::addActs().
        Fills a person's field $data['acts'][$actKey]
        with an associative array containing the transcription of the act stored in file BC.yml.
        If a new person is created, informations contained in the act are transferred to the person's field
        @param      $p      Person object or null
        @param      $actKey Can be 'birth', 'death' or 'mariage'
        @return     Person passed in parameter or new person (if $p is null).
    **/
    public static function personAct(Person|null $p, string $actKey, $actSlug): void {
        if($actKey != Act::BIRTH){
            throw new \Exception("'$actKey' not yet handled");
        }
        $newPerson = false;
        if(is_null($p)){
            $p = Person::createFromSlug($actSlug);
            if(is_null($p)){
                $p = new Person();
                $p->data['slug'] = $actSlug; // here person slug initialized by default with act slug.
                $newPerson = true;
            }
        }
        $parts = explode('-', $actSlug);
        $l = count($parts);
        $yamlFileName = match($actKey){     // php 8.0+
            Act::BIRTH => 'BC.yml',
            Act::DEATH => 'DC.yml',
            Act::MARIAGE => 'MC.yml',
        };
        // ex: $file = /path/to/acts/birth/1897/11/26/accard-robert-1897-11-26/BC.yml
        $file = self::computeFile($actKey, $actSlug);
        $act = yaml_parse_file($file);
        if($act === false){
            throw new \Exception("Unable to parse act file $file");
        }
        
        // transfer in $p->data the informations present in the act
        // act is considered more reliable than other sources, so replace existing data.
////////// here, do not do that, leave client code choose what to do
        $p->data = array_replace_recursive($p->data, $act['person']);
        $p->data = array_replace_recursive($p->data, $act['extras']);
        
        $p->data['acts'][$actKey] = $act;
        
        if($newPerson){
            if(
                isset($act['person']['name']['official']['family'])
                && !isset($act['person']['name']['family'])
            ){
                $p->data['name']['family'] = $act['person']['name']['official']['family'];
            }
            if(
                isset($act['person']['name']['official']['given'])
                && !isset($act['person']['name']['given'])
            ){
                $p->data['name']['given'] = $act['person']['name']['official']['given'];
            }
            if($actKey != Act::BIRTH){
                $p->data['trust'] = TRUST::CHECK;
            }
        }
        
        if($actKey == Act::BIRTH){
            $p->data['trust'] = TRUST::BC;
        }
        
        $p->addHistory(
            command: "wiki bc add $actSlug",
            sourceSlug: Act::SOURCE_SLUG,
            newdata: $act['person'],    // because person was updated
            rawdata: $act['person'],
        );
    }
    
} // end class
