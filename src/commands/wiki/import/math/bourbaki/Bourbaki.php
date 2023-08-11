<?php
/********************************************************************************
    Builds a list of 458 mathematicians ranked by eminence.
    Data source : book
        Éléments d'histoire des Mathématiques
        by Nicolas Bourbaki
        Ed. Springer
        2007 (3rd edition ; reprint from 1984 version, ed. Masson)
        Uses the "INDEX DES NOMS CITÉS", pp 366 - 376 of the book
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-11-26 21:59:17+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\import\math\bourbaki;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\{Source, Group};
use tiglib\arrays\sortByKey;

class Bourbaki {

    // TRUST_LEVEL not defined, using value of class Newalch
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION = 'eminence' . DS . 'math' . DS . 'bourbaki.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'bourbaki-history';
    
    // *********************** Source management ***********************
    
    /** @return a Source object for the raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** @return Path to the raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'eminence', 'math', 'bourbaki.txt']);
    }
    
    /** Loads raw file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
    
    // *********************** Tmp file manipulation ***********************
    
    /** @return Path to the csv file stored in data/tmp/ **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'eminence', 'math', 'bourbaki.csv']);
    }
    
}
