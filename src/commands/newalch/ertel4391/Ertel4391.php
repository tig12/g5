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
        Relative to directory data/model
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '3a_sports.yml';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'ertel4384athletes';
    
    /**
        Subgroups present in Ertel file
        Keys = group slugs
    **/
    const SUBGROUPS = [
        'GCPAR' => [
            'name' => '76 Para lowers',
            'description' => "76 athletes collected by Suitbert Ertel in Gauquelin laboratory,\n"
                . "not retained in Comité Para experiment because considered less eminent.\n"
                . "\"7 - Para lowers\" in Ertel 1988 article",
        ],
        'GMINI' => [
            'name' => '599 minor Italian footballers',
            'description' => "Unpublished by Gauquelin (not famous enough), copied manually by Ertel.\n"
                . "\"3 - Italian football\" in Ertel 1988 article",
        ],
        'GMING' => [
            'name' => '115 minor Germans sportsmen',
            'description' => "Unpublished by Gauquelin (not famous enough), copied manually by Ertel.\n"
                . "\"4 - German various\" in Ertel 1988 article",
        ],
        'G_ADD' => [
            'name' => '202 French sportsmen',
            'description' => "Copied manually by Ertel in Gauquelin's laboratory.\n"
                . "Considered as \"low-low-ranking\" by Gauquelin.\n"
                . "\"5 - French occasionals\" in Ertel 1988 article",
        ],
        'GMINV' => [
            'name' => '24 Italian cyclists',
            'description' => "Copied manually by Ertel in Gauquelin's laboratory.\n"
                . "\"10 - Italian cyclists\" in Ertel 1988 article",
        ],
        'GMIND' => [
            'name' => '453 French sportsmen',
            'description' => "Copied manually by Ertel in Gauquelin's laboratory.\n"
                . "\"11 - Lower French\" in Ertel 1988 article",
        ],
        'G_79F' => [
            'name' => '27 sportsmen',
            'description' => "Supplementary data sent by Gauquelin to Ertel after his visit in Paris.\n"
                . "\"13 - Plus special\" in Ertel 1988 article",
        ],
    ];
    
    // *********************** Source management ***********************
    
    /**
        Returns a Source object for the raw file used for Ertel4391.
    **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::RAW_SOURCE_DEFINITION);
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
    
    /** Returns a Group object for one of Ertel4391 subgroups. **/
    public static function getSubgroup($slug): Group {
        $g = new Group();
        $g->data['slug'] = $slug;
        $g->data['name'] = self::SUBGROUPS[$slug]['name'];
        $g->data['description'] = self::SUBGROUPS[$slug]['description'];
        $g->data['id'] = $g->insert();
        return $g;
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
    
} // end class
