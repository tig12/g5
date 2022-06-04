<?php
/********************************************************************************
    Code related to Gauquelin 1955 book "L'influence des astres"
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-05-08 23:39:19+02:00, Thierry Graff : creation
    @history    2019-04-08 15:24:04+02:00, Thierry Graff : Start generation of 2 versions : original and corrected
    @history    2022-05-25 22:53:23+02:00, Thierry Graff : New version, to start inclusion of minor painters and priests
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\app\Config;
// use g5\commands\gauq\LERRCP;
use tiglib\arrays\csvAssociative;

class G55 {
    
    /**
        List and characteristics of 1955 groups
            title: corresponds to the original title, effective number of records in the group may differ.
            lerrcp: LERRCP volume where memebers of a given group has been included ;
            rawfile: relative to data/raw/gauq/g55
    **/
    const GROUPS = [
        '576MED' => [
            'title' => "576 membres associés et correspondants de l'académie de médecine",
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '508MED' => [
            'title' => '508 autres médecins notables',
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '570SPO' => [
            'title' => '570 sportifs',
            'lerrcp' => 'A1',
            'occupation' => 'sportsperson',
        ],
        '676MIL' => [
            'title' => '676 militaires',
            'lerrcp' => 'A3',
            'occupation' => 'military-personnel',
        ],
        '906PEI' => [
            'title' => '906 peintres',
            'lerrcp' => 'A4',
            'occupation' => 'paintor',
        ],
        '361PEI' => [
            'title' => '361 peintres mineurs',
            //no lerrcp
            'occupation' => 'paintor',
            'raw-file' => 'g55-362-minor-painters.txt',
        ],
        '500ACT' => [
            'title' => '500 acteurs',
            'lerrcp' => 'A5',
            'occupation' => 'actor',
        ],
        '494DEP' => [
            'title' => '494 députés',
            'lerrcp' => 'A5',
            'occupation' => 'politician',
        ],
        '349SCI' => [
            'title' => "349 membres associés et correspondants de l'académie des sciences",
            'lerrcp' => 'A2',
            'occupation' => 'scientist',
        ],
        '884PRE' => [
            'title' => '884 prêtres',
            //no lerrcp
            'occupation' => 'catholic-priest',
        ],
        '369PRE' => [
            'title' => '369 prêtres du diocèse de Paris',
            //no lerrcp
            'occupation' => 'catholic-priest',
            'raw-file' => 'g55-369-priests-albi.txt',
        ],
        '513PRE' => [
            'title' => "513 prêtres du diocède d'Albi",
            //no lerrcp
            'occupation' => 'catholic-priest',
            'raw-file' => 'g55-513-priests-paris.txt',
        ],
    ];
    
    /**
        Returns the possible group keys that can be used to invoke commands raw2tmp and tmp2db
    **/
    public static function getPossibleGroupKeys() {
        $tmp = G55::GROUPS;
        unset($tmp['884PRE']);
        return array_keys($tmp);
    }
    
    
    // *********************** Raw files manipulation ***********************
    
    /** Separator used in raw files **/
    const RAW_SEP = ',';
    
    /** 
        Field names used in the raw files.
    **/
    const RAW_FIELDS = [
        'NAME',
        'DAY',
        'HOUR',
        'PLACE',
    ];
    
    /**
        Path to a raw file.
        @param  $groupKey a key of G55::GROUPS, like '570SPO'
        throws  Exception if raw file not defined for this group
    **/
    public static function rawFilename(string $groupKey): string {
        if(!isset(G55::GROUPS[$groupKey]['raw-file'])){
            throw new \Exception("G55 raw file not defined for group $groupKey (see constant G55::GROUPS).");
        }
        return implode(DS, [Config::$data['dirs']['raw'], 'gauq', 'g55', G55::GROUPS[$groupKey]['raw-file']]);
    }
    
    /**
        Loads a raw file in a regular array, each element contining one line.
        @param  $groupKey a key of G55::GROUPS, like '570SPO'
    **/
    public static function loadRawFile(string $groupKey): array {
        return file(self::rawFilename($groupKey));
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** 
        Field names used in the tmp files.
    **/
    const TMP_FIELDS = [
        'NUM',
        'FNAME',
        'GNAME',
        'DATE',
        'PLACE',
        'C1',
        'C2',
        'CY',
    ];
    
    /**
        Temporary file in data/tmp/gauq/g55/
        @param  $groupKey a key of G55::GROUPS, like '570SPO'
    **/
    public static function tmpFilename($groupKey){
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'g55', $groupKey . '.csv']);
    }
    
    /**
        Loads data/tmp/cfepp/cfepp-1120-nienhuys.csv in a regular array.
        Each element contains the person fields in an assoc. array.
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename(), G5::CSV_SEP);
    }
    
    // *********************** Tmp raw file manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", in data/tmp/gauq/g55/
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename($groupKey){
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'g55', $groupKey . '-raw.csv']);
    }
    
    /**
        Loads a "tmp raw file" in a regular array.
        Each element contains the person fields in an assoc. array.
    **/
    public static function loadTmpRawFile($groupKey){
        return csvAssociative::compute(self::tmpRawFilename($groupKey));
    }

} // end class    

