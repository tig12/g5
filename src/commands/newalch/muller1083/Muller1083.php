<?php
/******************************************************************************
    Code common to muller1083
    
    @license    GPL
    @history    2019-07-08 19:22:25+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller1083;

use g5\Config;
use g5\commands\newalch\Newalch;

use tiglib\arrays\csvAssociative;
use tiglib\strings\encode2utf8;

class Muller1083 {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 5a_muller_medics.txt.
        Relative to directory specified in config.yml by dirs / edited
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '5a_muller_medics.yml';
    
    /** Names of the columns of raw file 5a_muller_medics.txt **/
    const RAW_FIELDS = [
        'NR'        => 'Müller id, from 1 to 1083',
        'SAMPLE'    => 'Origin of the record',
        'GNR'       => 'Gauquelin NUM in A2 or E1',
        'CODE'      => '',
        'NAME'      => 'Family and given name',
        'GEBDATUM'  => 'Birth day',
        'JAHR'      => 'Birth year',
        'GEBZEIT'   => 'Birth hour',
        'GEBORT'    => 'Birth place',
        'LAENGE'    => 'Longitude',
        'BREITE'    => 'Latitude',
        'MODE'      => '"LMT" or empty',
        'KORR'      => 'Timezone offset',
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
        'NIENJU'    => '',
        'NIENSA'    => '',
    ];
    
    /** Names of the columns of data/tmp/cura/1083MED.csv **/
    const TMP_FIELDS = [
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
    
    
    // *********************** Source management ***********************
    
    /**
        Returns a Source object for the raw file used for Muller1083.
    **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::RAW_SOURCE_DEFINITION);
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
    
    /** Path to the temporary csv file keeping an exact copy of the raw file. **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'newalch', '1083MED-raw.csv']);
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
    
}// end class
