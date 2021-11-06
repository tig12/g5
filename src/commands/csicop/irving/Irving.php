<?php
/******************************************************************************
    File containing 408 sportsmen from CSICOP test.
    Sent by Kenneth Irving, originally coming from Dennis Rawlins
    
    @license    GPL
    @history    2019-12-23 00:38:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\app\Config;
use tiglib\arrays\csvAssociative;
use g5\model\DB5;
use g5\model\Source;

class Irving {
    
    /**                                            
        Default trust level associated to the persons of this group
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = DB5::TRUST_CHECK;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'csicop' . DS . 'rawlins-ertel-irving.yml';
    
    /** Slug of source  **/
    const LIST_SOURCE_SLUG = 'csi';
    
    // group definitions are located in class CSICOP
    
    const RAW_CSV_SEP = ';';
    
    /** Field names of data/raw/csicop/rawlins-ertel-irving/rawlins-ertel-irving.csv **/
    const RAW_FIELDS = [
        'Satz#',
        'NAME',
        'VORNAME',
        'GEBDAT',
        'GEBZEIT',
        'AMPM',
        'ZEITZONE',
        'GEBORT',
        'LO1',
        'LO2',
        'LA1',
        'LA2',
        'SPORTART',
        'MARS',
        'BATCH',
    ];
    
    /** Field names of data/tmp/csicop/irving/408-csicop-irving.csv **/
    const TMP_FIELDS = [
        'CSID',
        'GQID',
        'FNAME',
        'GNAME',
        'DATE',
        'TZO',
        'LG',
        'LAT',
        'C2',
        'CY',
        'SPORT',
        'MA36',
        'CANVAS',
    ];
    
    /** 
        Modifications done on ids during raw2tmp step.
        Purpose of these modifs is to have the same ids in SI42 and Irving.
        SI42 order was prefered to Irving because :
            - alphabetical order is respected
            - SI42 is the only known published group
        Format : Irving id => SI42 id.
        This correspondance is only used in raw2tmp.
        After this step, Irving ids and SI42 ids are identical.
    **/
    const IRVING_SI42 = [
        180 => 181,
        181 => 180,
        211 => 210,
        210 => 211,
        266 => 267,
        267 => 268,
        268 => 269,
        269 => 270,
        270 => 271,
        271 => 266,
        354 => 355,
        355 => 354,
    ];
    
    /** 
        Mapping between codes used in Irving file and g5 codes
    **/
    const SPORT_IRVING_G5 = [
        'AUTO' => 'motor-sports-competitor',
        'BASE' => 'baseball-player',
        'BASK' => 'basketball-player',
        'BOXI' => 'boxer',
        'FOOT' => 'american-football-player',
        'GOLF' => 'golfer',
        'GYMN' => 'gymnast',
        'HORS' => 'equestrian',
        'ICES' => 'winter-sports-practitioner',
        // Gauquelin AUT refers to "Auto moto"
        // The code MOTO refers to one record
        // A check on wikipedia shows that the discipline was in fact auto
        // https://fr.wikipedia.org/wiki/Cale_Yarborough
        // Associating MOTO to AUT then does not imply loss of information 
        'MOTO' => 'motor-sports-competitor',
        'RODE' => 'rodeo-rider',
        'SKII' => 'skier',
        'SWIM' => 'swimmer',
        'TENN' => 'tennis-player',
        'TRAC' => 'athletics-competitor',
        'VOLL' => 'volleyball-player',
        'WRES' => 'wrestler',
    ];
    
    // *********************** Source management ***********************
    
    /**
        Computes cura source and cura id within this source from field GQID.
        WARNING : returns cura source slug, not cura file name ('a2' and not 'A2')
        @param  $gnr String like "A6-1354"
        @return Array with 2 elements : cura source id and NUM
    **/
    public static function gqid2curaSourceId($GQID){
       [$curaFile, $NUM] = explode('-', $GQID);
       return [strtolower($curaFile), $NUM];
    }
    
    // *********************** Group management ***********************
    
    // See class CSICOP

    // *********************** Raw files manipulation ***********************
    
    /** Path to Irving's raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'csicop', 'rawlins-ertel-irving', 'rawlins-ertel-irving.csv']);
    }
    
    /** Loads rawlins-ertel-irving.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Temporary file in data/tmp/csicop/irving/ **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'csicop', 'irving', '408-csicop-irving.csv']);
    }
    
    /** Loads data/tmp/csicop/irving/408-csicop-irving.csv in a regular array **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename(), G5::CSV_SEP);
    }
    
    /** Loads data/tmp/csicop/irving/408-csicop-irving.csv in an asssociative array ; keys = CSID **/
    public static function loadTmpFile_csid(){
        $csv = self::loadTmpFile();
        $res = [];              
        foreach($csv as $row){
            $res[$row['CSID']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", data/tmp/csicop/irving/408-csicop-irving-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'csicop', 'irving', '408-csicop-irving-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }                                           

}// end class
