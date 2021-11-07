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
        Path to the yaml file containing the characteristics of Müller's booklet 3 famous women.
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
        Fields of data/tmp/gauq/g55/*.csv
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
    // *********************** Raw file manipulation ***********************
    // *********************** Tmp files manipulation ***********************
    // *********************** Tmp raw files manipulation ***********************
    
} // end class    
