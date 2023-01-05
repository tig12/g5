<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-03-22 21:09:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\app\Config;
use g5\model\Person;
use g5\model\Trust;

class Acts {
    
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
    public static function computeDir(string $key): string {
        return Config::$data['dirs']['acts'] . DS . $key;
    }
    
    /**
        Helper (delegate) for Person::addActs().
        Fills a person's field $data['acts'][$actKey]
        with an associative array containing the transcription of the act stored in file BC.yml.
        If a new person is created, informations contained in the act are transferred to the person's field
        @param      $p      Person object or null
        @param      $actKey Can be 'birth', 'death' or 'mariage'
        @return     Person passed in parameter or new person (if $p is null).
    **/
    public static function personAct(Person|null $p, string $actKey, $actSlug): Person {
        if($actKey != Acts::BIRTH){
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
        $yamlFileName = match($actKey){
            Acts::BIRTH => 'BC',
        };
        // ex: $file = /path/to/acts/birth/1897/11/26/accard-robert-1897-11-26/BC.yml
        $file = implode(DS, [
            self::computeDir($actKey),
            $parts[$l-3],
            $parts[$l-2],
            $parts[$l-1],
            $actSlug,
            $yamlFileName . '.yml',
        ]);
        $act = yaml_parse_file($file);
        if($act === false){
            throw new \Exception("Unable to parse act file $file");
        }
        
        // transfer in $p->data the informations present in the act
        $p->data = array_replace_recursive($p->data, $act['person']);
        
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
            if($actKey != Acts::BIRTH){
                $p->data['trust'] = TRUST::CHECK;
            }
        }
        
        $p->data['acts'][$actKey] = $act['document'];
        
        if($actKey == Acts::BIRTH){
            $p->data['trust'] = TRUST::BC;
        }
        
        $p->addHistory(
            command: "db fill act $actKey $actSlug", // TODO change when act command is stable,
            sourceSlug: Acts::SOURCE_SLUG,
            newdata: $act['person'],
            rawdata: $act['person'],
        );
        
        return $p;
    }
    
} // end class
