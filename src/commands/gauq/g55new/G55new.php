<?php
/********************************************************************************
    Contains code common to several g55new classes.
    This relates to 2 files not published in LERRCP booklets :
    minor painters and priests (from Albi and Paris diocèses).
    These files are not published in LERRCP series.
    Gauquelin id of the records are prefixed by
        55MP minor painters
        55PA priests Albi
        55PP priests Paris
    
    
    @license    GPL
    @history    2021-10-23 17:34:22+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\gauq\g55new;

class G55new {
    
    /**
        Path to the yaml file containing the characteristics of the sources describing raw files.
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILES = [
        'minor-painters'    => 'gauquelin' . DS . 'minor-painters.yml',
        'priests'           => 'gauquelin' . DS . 'priests',
    ];
    
    /** Slugs of sources for raw files **/
    const LIST_SOURCE_SLUGS = [
        'minor-painters'    => 'g55-minor-painters',
        'priests'           => 'g55-priests',
    ];
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet AFD3.
        Relative to directory data/db/source
    **/
    const BOOK_SOURCE_DEFINITION_FILE = 'gauquelin' . DS . '1955-book.yml';
    
    /** Slug of source "L'influence des astres" **/
    const BOOK_SOURCE_SLUG = 'gauquelin-1955-influence-des-astres';
    
    /** Slug of the groups in db **/
    const GROUP_SLUGS = [
        'minor-painters'    => 'g55-minor-painters',
        'priests'           => 'g55-priests',
    ];
    
    /**
        Fields of data/tmp/gauquelin1955/*.csv
        (all files have the same structure)
    **/
    const TMP_FIELDS = [
    ];
    
    // *********************** Id management ***********************
    /**
        Computes the LERRCP id of a record.
        @param  $key    'minor-painters' or 'priests'.
        @param  $recNo      Index of the record within the list
        @return A LERRCP id, like '55PR-456' or '55MP-24'.
    **/
    public static function lineNumber2LERRCPSourceId($key, $recNo){
        return ($key == 'minor-painters' ? '55MP' : '55PR') . '-' . $recNo;
    }

    // *********************** Group management ***********************
    
    /**
        Returns a Group object for priests or minor painters.
    **/
    public static function getGroup($key): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 1083 physicians";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "1083 physisicans of French Académie de médecine, gathered by Arno Müller";
        $g->data['sources'] = [self::LIST_SOURCE_SLUG, self::BOOKLET_SOURCE_SLUG];
        return $g;
    }

    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function rawFilename(){
        return Newalch::rawDirname() . DS . '05-muller-medics' . DS . '5a_muller-medics-utf8.txt';
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '1083MED.csv']);
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
    
    /**
        Returns the name of the "tmp raw file", eg. data/tmp/newalch/1083MED-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '1083MED-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class    
