<?php
/******************************************************************************
    File containing 408 sportsmen from CSICOP test.
    Sent by Kenneth Irving, originally coming from Dennis Rawlins
    
    @license    GPL
    @history    2019-12-23 00:38:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\Config;
use tiglib\arrays\csvAssociative;
use g5\model\DB5;
use g5\model\{Source, SourceI};

class Irving implements SourceI {
    
    /**                                            
        Default trust level associated to the persons of this group
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = DB5::TRUST_CHECK;
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / build
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'unpublished' . DS . 'rawlins-ertel-data.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'csicop';

    const RAW_CSV_SEP = ';';
    
    /** Field names of data/raw/csicop/irving/rawlins-ertel-data.csv **/
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
        'AUTO' => 'AUT',
        'BASE' => 'BOX',
        'BASK' => 'BAS',
        'BOXI' => 'BOX',
        'FOOT' => 'FOO',
        'GOLF' => 'GOL',
        'GYMN' => 'GYM',
        'HORS' => 'EQU',
        'ICES' => 'GLA',
        // Gauquelin AUT refers to "Auto moto"
        // The code MOTO refers to one record
        // A check on wikipedia shows that the discipline was in fact auto
        // https://fr.wikipedia.org/wiki/Cale_Yarborough
        // Associating MOTO to AUT then does not imply loss of information 
        'MOTO' => 'AUT',
        'RODE' => 'ROD',
        'SKII' => 'SKI',
        'SWIM' => 'NAT',
        'TENN' => 'TEN',
        'TRAC' => 'ATH',
        'VOLL' => 'VOL',
        'WRES' => 'LUT',
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for 5muller_writers.xlsx. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['build'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** Path to Irving's raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'csicop', 'irving', 'rawlins-ertel-data.csv']);
    }
    
    /** Loads rawlins-ertel-data.csv in a regular array **/
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
        Returns the name of a "tmp raw file", data/tmp/newalch/muller-402-it-writers-raw.csv
        (files used to keep trace of the original raw values).
        @param  $datafile : a string like 'A1'
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
