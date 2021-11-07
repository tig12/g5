<?php
/********************************************************************************
    Constants and utilities related to newalchemypress.org information source.
    This class is used by several subpackages (ertel, gauq, muller).
    
    @license    GPL
    @history    2020-07-13 17:15:37+02:00, Thierry Graff : creation
    @history    2020-08-12 19:46:54+02:00, Thierry Graff : adaptation for g5 database
********************************************************************************/
namespace g5\commands;

use g5\app\Config;
use g5\model\Source;
use g5\commands\muller\Muller;

class Newalch {
    
    /**
        Path to the yaml file containing the characteristics of Newalchemypress source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'newalch.yml';
    
    /**
        Slug of "newalch" source.
    **/
    const SOURCE_SLUG = 'newalch';
    
    /**
        Trust level for data coming from newalchemypress.com
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for newalchemypress web site. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** 
        Computes the name of the directory where raw cura files are stored
    **/
    public static function rawDirname(){
        return Config::$data['dirs']['raw'] . DS . 'newalchemypress.com';
    }
    
} // end class
