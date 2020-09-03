<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2020-07-13 17:15:37+02:00, Thierry Graff : creation
    @history    2020-08-12 19:46:54+02:00, Thierry Graff : adaptation for g5 database
********************************************************************************/
namespace g5\commands\newalch;

use g5\Config;
use g5\model\SourceI;
use g5\model\Source;

class Newalch implements SourceI {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / db
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch.yml';
    
    /**
        Trust level for data coming from newalchemypress.com
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    // *********************** Person ids ***********************
    /**
        Returns a unique Müller id, like "AFD5-33"
        Unique id of a record among newalch files.
        AFD means Astro-Forschungs-Daten
        5 means volume 5 of AFD (volumes from 1 to 5)
        33 is the id of the record in newalchemypress.org web site.
        See https://tig12.github.io/gauquelin5/newalch.html for precise definition
        @param $source      Slug of the source
        @param $NR          Value of field NR of a record within $source
    **/
    public static function muId($source, $NR){
        switch($source){
        	case '5muller_writers': 
        	    return 'AFD1-' . $NR;
        	break;
        	case '5a_muller_medics': 
        	    return 'AFD5-' . $NR;
        	break;
        }
        throw new \Exception("Invalid \$source parameter : $source");
    }
    
    /**
        Convenience method to find Müller id from Person's ids-in-source field
    **/
    public static function ids_in_sources2muId($ids_in_sources){
        if(isset($ids_in_sources['5muller_writers'])){
            return self::muId('5muller_writers', $ids_in_sources['5muller_writers']);
        }
        if(isset($ids_in_sources['5a_muller_medics'])){
            return self::muId('5a_muller_medics', $ids_in_sources['5a_muller_medics']);
        }
        return '';
    }
    
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
