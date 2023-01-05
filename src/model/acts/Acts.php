<?php
/********************************************************************************
    Constants and utilities.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-03-22 21:09:32+01:00, Thierry Graff : Creation
    @history    2023-01-01 20:23:00+01:00, Thierry Graff : Refactor, limit to mimimum
********************************************************************************/
namespace g5\model\acts;

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
    
} // end class
