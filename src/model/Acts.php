<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-03-22 21:09:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\app\Config;
use g5\model\Person;

class Acts {
    
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
        Fills a person's field $data['acts'][$key]
        with an associative array containing the transcription of the act
        stored in file BC.yml.
        @param      $p      Person object or person's slug
        @param      $key    Can be 'birth', 'death' or 'mariage'
        @return     Person passed in parameter or new person.
    **/
    public static function personAct(Person|string $p, string $key): Person {
        if($key != 'birth'){
            throw new \Exception("'$key' not yet handled");
        }
        if(!$p instanceof Person){
            $slug = $p;
            $p = Person::getBySlug($slug);
            if(is_null($p)){
                $p = new Person();
                $p->data['slug'] = $slug;
            }
        }
        $parts = explode('-', $p->data['slug']);
        $l = count($parts);
        $yamlFileName = match($key){
            'birth' => 'BC',
        };
        // ex: $file = /path/to/acts/birth/1897/11/26/accard-robert-1897-11-26/BC.yml
        $file = implode(DS, [
            self::computeDir($key),
            $parts[$l-3],
            $parts[$l-2],
            $parts[$l-1],
            $p->data['slug'],
            $yamlFileName . '.yml',
        ]);
        $p->data['acts'][$key] = yaml_parse_file($file);
        return $p;
    }
    
} // end class
