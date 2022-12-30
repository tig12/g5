<?php
/******************************************************************************
    Code common to afd4
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-15 22:45:44+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m4dynasties;

use g5\app\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;
use tiglib\strings\encode2utf8;
use g5\commands\Newalch;
use g5\commands\gauq\Cura5;

class M4dynasties {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file muller-1145-utf8.txt.
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd4-dynasties-list.yml';
    
    /** Slug of source muller-1145-utf8.txt **/
    const LIST_SOURCE_SLUG = 'afd4';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet 4 dynasties.
        Relative to directory data/db/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd4-dynasties-booklet.yml';
    
    /** Slug of source Astro-Forschungs-Daten vol 4 **/
    const BOOKLET_SOURCE_SLUG = 'afd4-booklet';
    
    /** Names of the columns of data/tmp/muller/4-dynasties/muller4-1145-dynasties.csv **/
    const TMP_FIELDS = [
        'MUID',
        'FNAME',
        'GNAME',
        'DATE',
        'TZO',
        'TIMOD',
        'PLACE',
        //CY;C1;C2;LAT;LG;OCCU;BOOKS;SOURCE;GQ

    ];
    
    // *********************** Group management ***********************
    
    /** Slug of Müller 4 group in db **/
    const GROUP_SLUG = 'muller-afd4-dynasties';
    
    /**
        Paths to the yaml file containing the characteristics of Müller 4 group.
        Relative to directory data/db/group
    **/
    const GROUP_DEFINITION_FILE = 'muller' . DS. self::GROUP_SLUG . '.yml';
    /**
        Returns a Group object for M5medics.
    **/
    public static function getGroup(): Group {
        return Group::createFromDefinitionFile(self::GROUP_DEFINITION_FILE);
    }

    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalchemypress.com
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', '4-dynasties', 'muller-1145-utf8.txt.zip']);
    }
    
    /**
        @return regular array, each element contains a line of the raw file
    **/
    public static function loadRawFile(){
        $zip = new \ZipArchive;
        $zipfile = self::rawFilename();
        $zip->open($zipfile);
        return explode("\n", $zip->getFromName(basename($zipfile, '.zip')));
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '4-dynasties', 'muller4-1145-dynasties.csv']);
    }
    
    /**
        Loads the temporary file in a regular array
        Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of the "tmp raw file", data/tmp/muller/4-dynasties/muller5-1083-medics-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', '4-dynasties', 'muller4-1145-dynasties-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class
