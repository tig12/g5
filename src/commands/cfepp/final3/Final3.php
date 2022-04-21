<?php
/******************************************************************************
    File containing 1120 sportsmen from CFEPP test.
    Sent by Jan Willem Nienhuis
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-20 17:59:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use tiglib\arrays\csvAssociative;
use g5\model\Trust;
use g5\model\Source;

class Final3 {
    
    /**                                            
        Default trust level associated to the persons of this group
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = Trust::CHECK;
    
    /** Slug of source  **/
    const LIST_SOURCE_SLUG = 'cfepp-final3';
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'cfepp' . DS . self::LIST_SOURCE_SLUG . '.yml';
    
    // group definitions are located in data/db/group/cfepp
    
    /**
        Map between sport labels in final3 and g5 occupation slugs.
    **/
    const RAW_OCCUS = [
        'Archery'           => 'archer',
        'Auto Racing'       => 'motor-sports-competitor',
        'Basketball'        => 'basketball-player',
        'Bowling'           => 'bowler',
        'Boxing'            => 'boxer',
        'Canoe-kayak'       => 'canoeist',
        'Cycling'           => 'cyclist',
        'Diving'            => 'diver',
        'Equestrian Sports' => 'equestrian',
        'Fencing'           => 'fencer',
        'Field Hockey'      => 'field-hockey-player',
        'Figure Skating'    => 'figure-skater',
        'Golf'              => 'golfer',
        'Gymnastics'        => 'gymnast',
        'Handball'          => 'handball-player',
        'Ice Hockey'        => 'ice-hockey-player',
        'Judo'              => 'judoka',
        'Modern Pentathlon' => 'modern-pentathlete',
        'Motorcycle racing' => 'motor-sports-competitor',
        'Mountain Climbing' => 'mountaineer',
        'Rowing'            => 'rower',
        'Rugby League'      => 'rugby-league-player',   // 13
        'Rugby Union'       => 'rugby-union-player',    // 15
        'Shooting'          => 'sport-shooter',
        'Skiing'            => 'skier',
        'Soccer'            => 'football-player',
        'Speed Skating'     => 'speed-skater',
        'Swimming'          => 'swimmer',
        'Table Tennis'      => 'table-tennis-player',
        'Tennis'            => 'tennis-player',
        'Track & Field'     => 'athletics-competitor',
        'Volleyball'        => 'volleyball-player',
        'Water Polo'        => 'water-polo-player',
        'Weightlifting'     => 'weightlifter',
        'Wrestling'         => 'wrestler',
        'Yachting'          => 'sport-sailer',
    ];
    
    /** 
        Field names used in the raw file.
        Exact names used by the CFEPP in their booklet.
    **/
    const RAW_FIELDS = [
        'SPORT',
        'SRC',          // source, see https://tig12.github.io/gauquelin5/cfepp.html
        'LV',           // level - see https://tig12.github.io/gauquelin5/cfepp.html
        'NAME',
        'LOC_DATE',     // local date
        'TR',           // time regime, can be 'f', 'o' or 'a'
        'LT',           // local time
        'BIRTH_PLACE',
        'POSTAL_CODE',
        'LONG',
        'LAT',
        'UNIV_DATE',
        'UT',
        'S',            // mars sector
    ];
    
    /**
        Limit of fields in the raw fields ; example for beginning of 4th line:
        Track & Field      A    FI   AITELLI Collette              1932 03 03 f 19 00   TOULON                    83056
        |                  |    |
        0                  19   24   etc.
    **/
    const RAW_LIMITS = [
        0,
        19,
        24,
        29,
        59,
        70,
        72,
        80,
        106,
        122,
        131,
        139,
        152,
        158,
        160, // EOL
    ];
    
    /** 
        Field names used in the tmp file.
    **/
    const TMP_FIELDS = [
        'CFID',
        'GQID',
        'ERID',
        'CPID',
        'OCCU',
        'SRC',
        'LV',
        'FNAME',
        'GNAME',
        'DATE',
// TODO         'TR',
        'DATE-UT',
        'PLACE',
        'C2',
        'C3',
        'LG',
        'LAT',
        'M12',
    ];
    
    
    // *********************** Source management ***********************
    
    // *********************** Group management ***********************
    
    // *********************** Raw files manipulation ***********************
    
    /** Path to final3 raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'cfepp', 'final3.zip']);
    }
    
    /**
        Loads file final3 in a regular array.
    **/
    public static function loadRawFile(){
        $zipfile = self::rawFilename();
        $zip = new \ZipArchive;
        $err = $zip->open($zipfile);
        if($err !== true){
            throw new \Exception("Unable to open $zipfile.\nError code: $err");
        }
        $content = $zip->getFromName(str_replace('.zip', '', basename($zipfile)));
        $zip->close();
        $lines = explode("\n", $content);
        return $lines;
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Temporary file in data/tmp/cfepp/cfepp-1120-nienhuys.csv **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'cfepp', 'cfepp-1120-nienhuys.csv']);
    }
    
    /** Loads data/tmp/cfepp/cfepp-1120-nienhuys.csv in a regular array **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename(), G5::CSV_SEP);
    }
    
    /** Loads data/tmp/cfepp/cfepp-1120-nienhuys.csv in an asssociative array ; keys = CFID **/
 
    public static function loadTmpFile_cfid(){
        $csv = self::loadTmpFile();
        $res = [];              
        foreach($csv as $cur){
            $res[$cur['CFID']] = $cur;
        }
        return $res;
    }

    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", data/tmp/cfepp/cfepp-1120-nienhuys-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'cfepp', 'cfepp-1120-nienhuys-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
    **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }                                           

} // end class
