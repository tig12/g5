<?php
/******************************************************************************
    
    Code relative to birth certificazte management
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-27 04:00:11+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model\wiki;

use g5\model\DB5;
use g5\model\wiki\Wiki;
use g5\app\Config;

class BC {
    
    /** Name of a file containing the description of a birth certificate **/
    const FILENAME = 'BC.yml';
    
    const SOURCE_LABEL = 'Birth certificate';
    
    /** **/
    const SOURCE_TYPE = 'birth-certificate';
    
    /** **/
    const SOURCE_SLUG = 'bc';
    
    /**
        Returns the path of a BC.yml file corresponding to a slug.
        Ex: data/wiki/persons/1811/10/25/galois-evariste-1811-10-25/BC.yml
        @param  $slug The slug of the person to add ; ex: galois-evariste-1811-10-25
    **/
    public static function filePath(string $slug) {
        return Config::$data['dirs']['wiki']  . DS . 'birth' . DS . Wiki::slug2dir($slug) . DS . self::FILENAME;
    }

    /**
        Checks if a BC.yml file contains valid informations
        @param  $yaml   Array containing information stored in a file BC.yml
        @return         Empty string if ok, error message if problems.
        TODO 
        https://github.com/rjbs/Rx
        https://rjbs.manxome.org/rx/
        https://github.com/romaricdrigon/MetaYaml
    **/
    public static function validate(array $yaml): string {
        return '';
    }
    

} // end class