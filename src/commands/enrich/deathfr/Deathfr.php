<?php
/******************************************************************************

    Contains code relate to more than one command
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-01-27 12:14:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;

class Deathfr {
    
    /**
        @return  Path to the tmp dir used by classes of this namespace.
    **/
    public static function tmpDir(): string {
        return Config::$data['dirs']['tmp'] . DS . 'enrich' .  DS . self::SOURCE_SLUG ;
    }
    
    // *********************** Source management ***********************
    
    /** Slug of this source **/
    const string SOURCE_SLUG = 'death-fr';
    
    /**
        Path to the yaml file containing the characteristics of the source describing CFEPP.
        Relative to directory data/db/source
    **/
    const string SOURCE_DEFINITION_FILE = 'enrich' . DS . self::SOURCE_SLUG . '.yml';
    
    /** Returns a Source object for raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Sqlite ***********************
    
    private static ?\PDO $sqlite = null;
    
    /** 
        Directory containing the intermediate sqlite database.
        Relative to Config::$data['dirs']['tmp']
    **/
    public static function sqlitePath(): string {
        return self::tmpDir() . DS . 'death-fr.sqlite3';
    }
    
    /** 
        @throws \PDOException
    **/
    public static function sqliteConnection(): ?\PDO {
        if(is_null(self::$sqlite)) {
            $path = self::sqlitePath();
            if(!is_file($path)){
                echo "ERROR: sqlite database '$path' does not exist\n"
                    . "Call first Deathfr::initializeSqlite() to create it\n";
                return null;
            }
            self::$sqlite = new \PDO('sqlite:' . $path);
        }
        return self::$sqlite;
    }

    
} // end class
