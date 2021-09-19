<?php
/******************************************************************************
    Arno Müller's 402 italian writers
    Code common to muller402
    
    @license    GPL
    @history    2020-05-15 ~22h30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\app\Config;
use g5\model\DB5;
use g5\model\{Source, Group};
use tiglib\time\seconds2HHMMSS;
use tiglib\arrays\csvAssociative;

class Muller402 {
    
    // TRUST_LEVEL not defined, using value of class Newalch
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/newalchemypress.com/05-muller-writers/5muller_writers.csv
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd1-writers-list-402.yml';

    /** Slug of source 5muller_writers.csv **/
    const LIST_SOURCE_SLUG = 'afd1';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet AFD1.
        Relative to directory data/db/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd1-writers-booklet.yml';
    
    /** Slug of source Astro-Forschungs-Daten vol 1 **/
    const BOOKLET_SOURCE_SLUG = 'afd1-booklet';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller-afd1-writers';

    /** Separator used in the raw csv file **/
    const RAW_SEP = ';';
    
    /** Names of the columns of raw file 5muller_writers.csv **/
    const RAW_FIELDS = [
        'NAME',
        'YEAR',
        'MONTH',
        'DAY',
        'HOUR',
        'MIN',
        'TZO',
        'PLACE',
        'LAT',
        'LG',
    ];
    
    /** Names of the columns of raw file data/tmp/newalch/muller-402-it-writers.csv **/
    const TMP_FIELDS = [
        'MUID',
        'GQID',
        'FNAME',
        'GNAME',
        'SEX',
        'DATE',
        'TZO',
        'LMT',
        'PLACE',
        'CY',
        'C2',
        'LG',
        'LAT',
        'OCCU',
    ];
    
    // *********************** Source management ***********************
    
    /**
        Computes cura source and cura id within this source from field GQID.
        WARNING : returns cura source slug, not cura file name ('a2' and not 'A2')
        @param  $gnr String like "A6-1354"
        @return Array with 2 elements : cura source id and NUM (ex: a6 and 1354)
    **/
    public static function gqid2curaSourceId($GQID){
       [$curaFile, $NUM] = explode('-', $GQID);
       return [strtolower($curaFile), $NUM];
    }

    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller402.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 402 Italian writers";
        $g->data['description'] = "402 Italian writers, gathered by Arno Müller";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['sources'] = [self::LIST_SOURCE_SLUG, self::BOOKLET_SOURCE_SLUG];
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'newalchemypress.com', '05-muller-writers', '5muller_writers.csv']);
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
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the tmp file in an asssociative array.
            keys = Müller ids (MUID)
            values = assoc array containing the fields
    **/
    public static function loadTmpFile_id(){
        $rows1 = csvAssociative::compute(self::tmpFilename());
        $res = [];
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }                                                                                              
    
    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file" : data/tmp/newalch/muller-402-it-writers-raw.csv
        (files used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', 'muller-402-it-writers-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }

    // *********************** time / space functions ***********************
    // shared by Muller402 and Muller100
    
    /**
        Conversion of TZ offset found in newalch file to standard sHH:MM offset.
        WARNING : possible mistake for "-0.6" :
            0.6*60 = 36
            "Problèmes de l'heure résolus pour le monde entier",
            Françoise Schneider-Gauquelin (p 288) indicates 00:37
            Current implementation uses Gauquelin, but needs to be confirmed
        @param $offset  timezone offset as specified in newalch file
        @param $lg      longitude, as previously computed
    **/
    public static function compute_offset($offset, $lg){
        if($offset == 'LMT'){ 
            // happens for 5 records
            // convert longitude to HH:MM:SS
            $sec = $lg * 240; // 240 = 24 * 3600 / 360
            return '+' . seconds2HHMMSS::compute($sec);
        }
        switch($offset){
        	case '-1':
        	case '-1.00':
        	    return '+01:00';
        	break;
        	case '-0,83': 
        	case '-0.83': 
        	    return '+00:50';
        	break;
        	case '-0,88': 
        	case '-0.88': 
        	    // Converting geonames.org longitude for Palermo (13°20'08") gives 00:53:34
        	    // Gauquelin says 00:54
        	    // Gabriel says 00:53:28
        	    return '+00:54';
        	break;
        	case '-0,6': 
        	    return '+00:37';
        	break;
            default:
                throw new \Exception("Timezone offset not handled in Muller402 : $offset");
        }
    }
}// end class
