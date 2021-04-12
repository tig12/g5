<?php
/******************************************************************************
    Arno Müller's 234 famous women
    Code common to afd3
    
    @license    GPL
    @history    2020-05-15 ~22h30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3;

use g5\Config;
use g5\model\DB5;
use g5\model\{Source, SourceI, Group};
//use tiglib\time\seconds2HHMMSS;
//use tiglib\arrays\csvAssociative;

class AFD3 implements SourceI {
    
    /**
        Trust level for data
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'booklet' . DS . 'AFD' . DS . 'muller-afd3.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller234women';

    /**
        Limit of fields in the raw fields ; example for beginning of first line:
        001 ADAM, Juliette *LAMBER                      04.10.1836 23.00       LMT  F   Verberie (Oise)
        |   |                                           |                      |
        0   4                                           48                     59
    **/
    const RAW_LIMITS = [
        0,
        4,
        48,
        59,
        65,
        71,
        76,
        80,
        112,
        120,
        129,
        132,
        135,
        144,
        147,
        149,
    ];
    
    /** Names of the columns of raw file **/
    const RAW_FIELDS = [
        'MUID',
        'NAME',
        'DATE',
        'TIME',
        'TZO',
        'TIMOD', // time mode
        'COU',
        'PLACE',
        'LAT',
        'LG',
        'OCCU',
        'BERUF',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** Names of the columns of tmp csv file **/
    const TMP_FIELDS = [
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller234.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 234 famous women";
        $g->data['description'] = "234 famous women, gathered by Arno Müller";
        $g->data['id'] = $g->insert();
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', 'afd3-women', 'muller-afd3-women.txt']);
    }
    
    /** Loads 5muller_writers.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
                                                                                         
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd3-women', 'muller-afd3-women.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
} // end class
