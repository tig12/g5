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
use g5\model\Group;
use tiglib\arrays\csvAssociative;
use g5\commands\newalch\Newalch;

class Ertel4391 implements SourceI {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 3a_sports.txt.
        Relative to directory specified in config.yml by dirs / build
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '3a_sports.yml';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'ertel4384athletes';
    
    const SPORT_ERTEL_G5 = [
        'AIRP' => 'AVI',
        'ALPI' => '',
        'AUTO' => '',
        'AVIR' => '',
        'BADM' => '',
        'BASE' => '',
        'BASK' => '',
        'BILL' => '',
        'BOBS' => '',
        'BOWL' => '',
        'BOXI' => '',
        'CANO' => '',
        'CYCL' => '',
        'FENC' => '',
        'FOOT' => '',
        'GOLF' => '',
        'GYMN' => '',
        'HAND' => '',
        'HOCK' => '',
        'HORS' => '',
        'ICES' => '',
        'JUDO' => '',
        'MOTO' => '',
        'PELO' => '',
        'RODE' => '',
        'ROLL' => '',
        'ROWI' => '',
        'RUGB' => '',
        'SHOO' => '',
        'SKII' => '',
        'SWIM' => '',
        'TENN' => '',
        'TRAC' => 'ATH',
        'TRAV' => '',
        'VOLL' => '',
        'WALK' => '',
        'WEIG' => '',
        'WRES' => '',
        'YACH' => '',
    ];
    
    
    // *********************** Source management ***********************
    
    /**
        Returns a Source object for the raw file used for Ertel4391.
    **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['build'] . DS . self::RAW_SOURCE_DEFINITION);
    }

    // *********************** Group management ***********************
    
    /** Returns a Group object for Ertel4391. **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Ertel 4384 athletes";
        $g->data['description'] = "4384 athletes compiled by Suitbert Ertel\n(Ertel says 4391)";
        $g->data['id'] = $g->insert();
        return $g;
    }

    /** Returns a Group object for "para lowers". **/
/* 
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = 'ParaLowers';
        $g->data['name'] = "Ertel 4384 athletes";
        $g->data['description'] = "4384 athletes compiled by Suitbert Ertel\n(Ertel says 4391)";
        $g->data['id'] = $g->insert();
        return $g;
    }
*/

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
    
    // *********************** Tmp raw files manipulation ***********************
    
    /** Path to the temporary csv file keeping an exact copy of the raw file. **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '4391SPO-raw.csv']);
    }
    
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
}// end class
