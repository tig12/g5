<?php
/******************************************************************************
    Code common to muller1083
    
    @license    GPL
    @history    2019-07-08 19:22:25+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller1083;

use g5\Config;
use g5\commands\cura\Cura;


use tiglib\arrays\csvAssociative;
use tiglib\strings\encode2utf8;

class Muller1083{
    
    /** Name of the csv file in 5-newalch-csv/ **/
    const TMP_CSV_FILE = '1083MED.csv';
    
    /**
        Columns of raw file 5a_muller_medics.txt
        associated with their meanings
    **/
    const RAW_COLUMNS = [
        'NR'        => 'Müller id, from 1 to 1083',
        'SAMPLE'    => 'Origin of the record',
        'GNR'       => 'Gauquelin NUM in A2 or E1',
        'CODE'      => '',
        'NAME'      => 'Family and given name',
        'GEBDATUM'  => 'Birth day',
        'JAHR'      => 'Birth year',
        'GEBZEIT'   => '',
        'GEBORT'    => '',
        'LAENGE'    => '',
        'BREITE'    => '',
        'MODE'      => '',
        'KORR'      => '',
        'ELECTDAT'  => 'Date of election in Académie de médecine',
        'ELECTAGE'  => 'Age of election',
        'STBDATUM'  => '',
        'SONNE'     => '',
        'MOND'      => '',
        'VENUS'     => '',
        'MARS'      => '',
        'JUPITER'   => '',
        'SATURN'    => '',
        'SO_'       => '',
        'MO_'       => '',
        'VE_'       => '',
        'MA_'       => '',
        'JU_'       => '',
        'SA_'       => '',
        'PHAS_'     => '',
        'AUFAB'     => '',
        'NIENMO'    => '',
        'NIENVE'    => '',
        'NIENMA'    => '',
    ];
    
    const SAMPLE_CODE = [
            'MUER_NUR'   => '1',
            'MUERGAUQ-d' => '2',
            'MUERGAUQ'   => '3',
            'GAUQ_NUR'   => '4',                    
    ];
    const SAMPLE_GNR = [
            'MUER_NUR'   => 'N',
            'MUERGAUQ-d' => 'Y',
            'MUERGAUQ'   => 'Y',
            'GAUQ_NUR'   => 'Y',
    ];
    
    
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
    
    // ******************************************************
    /**
        @return Path to the raw file coming from newalch
    **/
    public static function raw_filename(){
        return Config::$data['dirs']['1-newalch-raw'] . DS . '05-muller-medics' . DS . '5a_muller-medics-utf8.txt';
    }
    
    // ******************************************************
    /**
        @return Path to the csv file stored in 5-newalch-csv
    **/
    public static function tmp_csv_filename(){
        return Config::$data['dirs']['5-newalch-csv'] . DS . self::TMP_CSV_FILE;
    }
    
    // ******************************************************
    /**
        Loads file 5-newalch-csv/1083MED.csv in a regular array
        @return Regular array
                Each element contains an associative array (keys = field names).
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmp_csv_filename());
    }                                                                                              
    
    // ******************************************************
    /**
        Loads file 5-newalch-csv/1083MED.csv in an asssociative array ; keys = NR
    **/
    public static function loadTmpFile_nr(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['NR']] = $row;
        }
        return $res;
    }
    
}// end class
