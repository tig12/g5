<?php
/******************************************************************************
    Code common to ertel4391
    
    @license    GPL
    @history    2019-05-11 23:15:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\ertel4391;

use g5\Config;
use g5\model\SourceI;
use g5\model\Source;
use tiglib\arrays\csvAssociative;
use g5\commands\newalch\Newalch;

class Ertel4391 implements SourceI {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 3a_sports.txt.
        Relative to directory specified in config.yml by dirs / edited
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '3a_sports.yml';
    
    // *********************** Source management ***********************
    
    /**
        Returns a Source object for the raw file used for Ertel4391.
    **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['edited'] . DS . self::RAW_SOURCE_DEFINITION);
    }

    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function rawFilename(){
        return Newalch::rawDirname() . DS . '03-ertel' . DS . '3a_sports-utf8.txt';
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '4391SPO.csv']);
    }
    
    /** Path to the temporary csv file keeping an exact copy of the raw file. **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '4391SPO-raw.csv']);
    }
    
    /**
        Loads the temporary file in a regular array
        Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the file temporary file in an asssociative array ; keys = NR
    **/
    public static function loadTmpFile_nr(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['NR']] = $row;
        }
        return $res;
    }
    
}// end class
