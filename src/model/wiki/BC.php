<?php
/******************************************************************************
    
    Code relative to birth certificazte management
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-27 04:00:11+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model\wiki;

use g5\model\wiki\Wiki;
use g5\model\wiki\WikiPerson;
use g5\model\DB5;
use g5\G5;

class BC {
    
    /** Name of a file containing the description of a birth certificate **/
    const TEMPLATE_FILENAME = 'BC.yml';
    
    /** Key used in person's field $data['acts'] to store a BC **/
    const PERSON_ACT_KEY = 'birth';
    
    /**
        @return Path to the template file BC.yml - src/model/wiki/templates/BC.yml.
    **/
    public static function templateFilePath(){
        return G5::ROOT_DIR . DS . implode(DS, ['model', 'wiki', 'templates', BC::TEMPLATE_FILENAME]);
    }

    /**
        @return Path to the directory containing birth certificates - by default data/wiki/person.
    **/
    public static function rootDir(){
        return WikiPerson::rootDir();
    }

    /**
        Returns the path of the directory corresponding to a slug.
        Ex: data/wiki/person/1811/10/25/galois-evariste-1811-10-25
        @param  $personSlug The slug of the person to add ; ex: galois-evariste-1811-10-25
    **/
    public static function dirPath(string $personSlug) {
        return BC::rootDir() . DS . Wiki::slug2dir($personSlug);
    }

    /**
        Returns the path of a BC.yml file corresponding to a slug.
        Ex: data/wiki/person/1811/10/25/galois-evariste-1811-10-25/BC.yml
        @param  $personSlug The slug of the person to add ; ex: galois-evariste-1811-10-25
    **/
    public static function filePath(string $personSlug) {
        return BC::dirPath($personSlug) . DS . BC::TEMPLATE_FILENAME;
    }
    
    /**
        Creates a BC from a file BC.yml
    **/
    public static function createFromYamlFile($yamlFile) {
        $BC = yaml_parse_file($yamlFile);
        $BC['slug'] = basename(dirname($yamlFile));
        return $BC;
    }

    /**
        Checks if a BC.yml file contains valid informations
        TODO implement
        @param  $yaml   Array containing information stored in a file BC.yml
        @return         Empty string if ok, error message if problems.
        https://github.com/rjbs/Rx
        https://rjbs.manxome.org/rx/
        https://github.com/romaricdrigon/MetaYaml
    **/
    public static function validate(array $yaml): string {
        // TODO implement
        return '';
    }
    
} // end class