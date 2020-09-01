<?php
/******************************************************************************
    Code common to muller1083
    
    @license    GPL
    @history    2019-07-08 19:22:25+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller1083;

use g5\Config;
use g5\model\SourceI;
use g5\model\Source;
use tiglib\arrays\csvAssociative;
use tiglib\strings\encode2utf8;
use g5\commands\newalch\Newalch;
use g5\commands\cura\Cura;

class Muller1083 implements SourceI {
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 5a_muller_medics.txt.
        Relative to directory specified in config.yml by dirs / db
    **/
    const RAW_SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'newalch' . DS . '5a_muller_medics.yml';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller1083medics';
    
    /**
        Names of the columns of raw file 5a_muller_medics.txt
        and their meanings (when known).
    **/
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
        'NOB', // nobiliary-particle
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
    
    // ******************************************************
    /**
        @param  $gnr String like "SA22" or "ND129"
        @return Array with 2 elements : cura file and NUM
    **/
    public static function gnr2cura($GNR){
        $curaPrefix = substr($GNR, 0, 3); // SA2 or ND1
        $curaFilename = ($curaPrefix == 'SA2' ? 'A2' : 'E1');
        $NUM = substr($GNR, 3);
        return [$curaFilename, $NUM];
    }

}// end class
