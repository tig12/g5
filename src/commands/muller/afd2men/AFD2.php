<?php
/******************************************************************************
    Arno Müller's 612 famous men
    Code common to afd2
    
    @license    GPL
    @history    2021-09-05 04:36:32+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd2men;

use g5\Config;
use g5\model\DB5;
use g5\model\{Source, Group};
use tiglib\arrays\csvAssociative;

class AFD2 {
    
    /**
        Trust level for data
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/muller/afd2-men/muller-afd2-men.txt
        Relative to directory data/model/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd2-men-list.yml';

    /** Slug of source muller-afd2-men.txt **/
    const LIST_SOURCE_SLUG = 'afd2';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet AFD2.
        Relative to directory data/model/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd2-men-booklet.yml';
    
    /** Slug of source Astro-Forschungs-Daten vol 2 **/
    const BOOKLET_SOURCE_SLUG = 'afd2-booklet';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller-02-men';

    /**
        Limit of fields in the raw fields ; example for beginning of first line:
        1   Abbe, Ernst                         23.01.1840 21.30        LMT D   Eisenach
        |   |                                   |               |      |
        0   4                                   40              57     64
    **/
    const RAW_LIMITS = [
        0,
        4,
        40,
        51,
        57,
        64,
        68,
        71,
        96,
        103,
        113,
        118,
        124,
        127,
        128,
    ];
    
    /** Names of the columns of raw file **/
    const RAW_FIELDS = [
        'MUID',
        'NAME',
        'DATE',
        'TIME',
        'TZO',
        'TIMOD', // time mode
        'CY',
        'PLACE',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** Names of the columns of tmp csv file **/
    const TMP_FIELDS = [
        'MUID',
        'FNAME',
        'GNAME',
        'NOBL',
        'DATE',
        'TZO',
        'TIMOD', // time mode
        'PLACE',
        'CY',
        'C1',
        'C2',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** 
        Match between Müller and Cura ids.
        Array built by look::look_gauquelin()
        Used by tmp2db::execute()
    **/
    const MU_GQ = [
    ];
    
    /** 
        Associations Müller's Berufsgruppe / Tätigkeitsfeld => g5 occupation code
        Partly built by look::look_occus().
        Note: sometimes doesn't follow Müller, after checking on wikipedia.
        X means useless because handled by tweaks file.
    **/
    const OCCUS = [
/* 
        'AR 01' => 'fictional-writer', // 85 persons
        'AR 02' => 'factual-writer', // 12 persons
        'AR 03' => 'actor', // 43 persons
        'AR 04' => 'composer', // 1 persons
        'AR 06' => 'singer', // 21 persons
        'AR 07' => 'musician', // 3 persons
        'AR 08' => 'X', // 11 persons - more precise infos in tweaks file
        'SC 01' => 'mathematician', // 1 persons
        'SC 02' => 'X', // 1 persons - Irène Joliot-Curie - more precise infos in tweaks file
        'SC 03' => 'X', // 2 persons - more precise infos in tweaks file
        'SC 04' => 'physician', // 2 persons
        'SC 05' => 'social-scientist', // 8 persons
        'SC 06' => 'historian-of-science', // 1 persons
        'SC 07' => 'romanist', // 1 persons
        'WA 02' => 'aircraft-pilot', // 2 persons
        'WA 04' => 'politician', // 7 persons
        'WA 05' => 'religious-leader', // 2 persons
        'WA 06' => 'monarch', // 10 persons
        'WA 08' => 'revolutionary', // 2 persons
        'WA 09' => 'X', // 4 persons - more precise infos in tweaks file
        'WA 10' => 'suffragette', // 7 persons
        'WA 12' => 'partner-of-celebrity', // 8 persons
*/
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller612.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 612 famous men";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "612 famous men, gathered by Arno Müller";
        $g->data['sources'][] = self::LIST_SOURCE_SLUG;
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file, built from scans.
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', 'afd2-men', 'muller-afd2-men.txt']);
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
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd2-men', 'muller-afd2-men.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the file temporary file in an asssociative array ; keys = MUID
    **/
    public static function loadTmpFile_muid(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of the "tmp raw file", eg. data/tmp/newalch/1083MED-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd2-men', 'muller-afd2-men-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class
