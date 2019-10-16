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
        To fix GNR column using cura A2 and E1 information.
        In the Müller's file, GNR is truncated, so GNR superior to 999 are erroneous.
        List built using php run-g5.php newalch muller1083 look a2names
@todo suppress, use directly in fixGnr
    **/
    const FIX_GNR = [
        42 => 'SA22558',
        342 => 'SA2255',
        137 => 'SA2101',
        422 => 'SA21017',
        138 => 'SA2103',
        451 => 'SA21033',
        139 => 'SA2104',
        464 => 'SA21043',
        472 => 'SA21045',
        478 => 'SA21049',
        141 => 'SA2105',
        484 => 'SA21051',
        143 => 'SA2107',
        534 => 'SA21070',
        146 => 'SA2110',
        603 => 'SA21100',
        617 => 'SA21107',
        622 => 'SA21109',
        149 => 'SA2112',
        678 => 'SA21127',
        679 => 'SA21128',
        150 => 'SA2113',
        685 => 'SA21130',
        153 => 'SA2115',
        741 => 'SA21155',
        154 => 'SA2116',
        761 => 'SA21168',
        158 => 'SA2118',
        815 => 'SA21186',
        162 => 'SA2121',
        880 => 'SA21215',
        163 => 'SA2122',
        905 => 'SA21227',
        918 => 'SA21229',
        168 => 'SA2126',
        966 => 'SA21267',
        171 => 'SA2128',
        1024 => 'SA21283',
        172 => 'SA2129',
        1042 => 'SA21296',
        369 => 'SA2274',
        506 => 'SA22744',
        515 => 'SA21064',
        520 => 'SA21067',
        524 => 'SA21069',
        523 => 'ND11113',
        525 => 'ND11116',
        605 => 'ND11260',
        610 => 'ND11267',
        668 => 'ND11366',
        669 => 'ND11367',
        743 => 'ND11520',
        752 => 'ND11526',
        753 => 'ND11527',
        849 => 'ND11687',
        852 => 'ND11688',
        913 => 'ND11804',
        914 => 'ND11806',
        980 => 'ND11971',
        984 => 'ND11979',
        1020 => 'ND12035',
        1022 => 'ND12037',
        1025 => 'ND12042',
        1028 => 'ND12049',
        1037 => 'ND12070',
        1040 => 'ND12076',
        1044 => 'ND12080',
        1046 => 'ND12089',
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
