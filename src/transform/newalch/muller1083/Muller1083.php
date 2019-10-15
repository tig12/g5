<?php
/******************************************************************************
    Code common to muller1083
    
    @license    GPL
    @history    2019-07-08 19:22:25+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\Config;
use g5\transform\cura\Cura;


use tiglib\arrays\csvAssociative;
use tiglib\strings\encode2utf8;

class Muller1083{
    
    /** Name of the csv file in 5-newalch-csv/ **/
    const TMP_CSV_FILE = '1083MED.csv';
    
    /** Columns of TMP_CSV_FILE **/
    const TMP_CSV_COLUMNS = [
        'NR',
        'SAMPLE',
        'GNR',
        'CODE',
        'FNAME',
        'GNAME',
        'DATE',
        'PLACE',
        'C2',
        'LG',
        'LAT',
        'MODE',
        'KORR',
        'ELECTDAT',
        'STBDATUM',
        'SONNE',
        'MOND',
        'VENUS',
        'MARS',
        'JUPITER',
        'SATURN',
        'SO_',
        'MO_',
        'VE_',
        'MA_',
        'JU_',
        'SA_',
        'PHAS_',
        'AUFAB',
        'NIENMO',
        'NIENVE',
        'NIENMA',
        'NIENJU',
        'NIENSA',
        'NOTES'
    ];
    
    /** 
        Associations NR => corrected GNR
        To fix GNR column using cura A2 information.
        In the Müller's file, GNR is truncated, so GNR superior to 999 are erroneous.
        List built using php run-g5.php newalch muller1083 look a2names
        These corrections are integrated in raw2csv
    **/
    const FIX_GNR = [
        422 => 1017,
        451 => 1033,
        478 => 1049,
        484 => 1051,
        524 => 1069,
        534 => 1070,
        622 => 1109,
        679 => 1128,
        685 => 1130,
        741 => 1155,
        761 => 1168,
        815 => 1186,
        880 => 1215,
        918 => 1229,
        966 => 1267,
        1024 =>1283,
        1042 =>1296,
        506 => 2744,
    ]; 
    // ******************************************************
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function raw_filename(){
        return Config::$data['dirs']['1-newalch-raw'] . DS . '05-muller-medics' . DS . '5a_muller-medics-utf8.txt';
    }
    
    
    // ******************************************************
    /**
        Loads file 5-newalch-csv/1083MED.csv.
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . self::TMP_CSV_FILE);
    }                                                                                              
    
}// end class
