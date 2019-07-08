<?php
/******************************************************************************
    Code common to muller1083
    
    @license    GPL
    @history    2019-07-08 19:22:25+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\Config;
use tiglib\arrays\csvAssociative;

class Muller1083{
    
    /** Name of the csv file in 5-tmp/newalch-csv **/
    const TMP_CSV_FILE = '1083MED.csv';
    
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
    ];
    
    
    // ******************************************************
    /**
        Loads file 5-tmp/newalch-csv/1083MED.csv.
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(Config::$data['dirs']['5-newalch-csv'] . DS . self::TMP_CSV_FILE);
    }                                                                                              
    
}// end class
