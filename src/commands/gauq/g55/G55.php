<?php
/********************************************************************************
    Code related to the inclusion of data contained in Gauquelin 1955 book "L'influence des astres"
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-05-08 23:39:19+02:00, Thierry Graff : creation
    @history    2019-04-08 15:24:04+02:00, Thierry Graff : Start generation of 2 versions : original and corrected
    @history    2022-05-25 22:53:23+02:00, Thierry Graff : New version, to start inclusion of minor painters and priests
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\G5;
use g5\app\Config;
// use g5\commands\gauq\LERRCP;
use tiglib\arrays\csvAssociative;

class G55 {
    
    // *********************** G55 unique id ***********************
    
    /**
        Returns a unique Gauquelin 1955 id, like "570SPO-123"
        Unique id of a record among birth dates published in Gauquelin's 1955 book.
        See https://tig12.github.io/gauquelin5/g55.html for precise definition.
        @param $groupKey    String like '570SPO', one of the key of G55:GROUPS
        @param $N           Value of field NUM of a record within the group
    **/
    public static function g55Id($groupKey, $N){
        return "$groupKey-$N";
    }
    
    
    // *********************** Source management ***********************
    // When Gauquelin 1955 book is considered as an information source.
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'gauq' . DS . 'g55' . DS . 'g55.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'g55';
    
    // *********************** Group management ***********************
    
    /**
        List and characteristics of 1955 groups
            slug:       slug of the group in g5 database.
                        Convention: source slug = group slug
            title:      corresponds to the original title, effective number of records in the group may differ.
            lerrcp:     LERRCP volume where memebers of a given group has been included ;
            rawfile:    relative to data/raw/gauq/g55
    **/
    const GROUPS = [
        '576MED' => [
            'slug' => 'g55-576-medics',
            'title' => "576 membres associés et correspondants de l'académie de médecine",
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '508MED' => [
            'slug' => 'g55-508-medics',
            'title' => '508 autres médecins notables',
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '570SPO' => [
            'slug' => 'g55-570-sportsmen',
            'title' => '570 sportifs',
            'lerrcp' => 'A1',
            'occupation' => 'sportsperson',
        ],
        '676MIL' => [
            'slug' => 'g55-676-military',
            'title' => '676 militaires',
            'lerrcp' => 'A3',
            'occupation' => 'military-personnel',
        ],
        '906PEI' => [
            'slug' => 'g55-906-painters',
            'title' => '906 peintres',
            'lerrcp' => 'A4',
            'occupation' => 'painter',
        ],
        '361PEI' => [
            'slug' => 'g55-362-minor-painters',
            'title' => '361 peintres mineurs',
            //no lerrcp
            'occupation' => 'painter',
            'raw-file' => 'g55-362-minor-painters.txt',
        ],
        '500ACT' => [
            'slug' => 'g55-500-actors',
            'title' => '500 acteurs',
            'lerrcp' => 'A5',
            'occupation' => 'actor',
        ],
        '494DEP' => [
            'slug' => 'g55-494-politicians',
            'title' => '494 députés',
            'lerrcp' => 'A5',
            'occupation' => 'politician',
        ],
        '349SCI' => [
            'slug' => 'g55-349-scientists',
            'title' => "349 membres associés et correspondants de l'académie des sciences",
            'lerrcp' => 'A2',
            'occupation' => 'scientist',
        ],
        '882PRE' => [
            'slug' => 'g55-882-priests',
            'title' => '882 prêtres',
            //no lerrcp
            'occupation' => 'catholic-priest',
        ],
        '513PRE' => [
            'slug' => 'g55-513-priests-paris',
            'title' => '513 prêtres du diocèse de Paris',
            //no lerrcp
            'occupation' => 'catholic-priest',
            'raw-file' => 'g55-513-priests-paris.txt',
        ],
        '369PRE' => [
            'slug' => 'g55-369-priests-albi',
            'title' => "369 prêtres du diocède d'Albi",
            //no lerrcp
            'occupation' => 'catholic-priest',
            'raw-file' => 'g55-369-priests-albi.txt',
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
        'GQID',
        'FNAME',
        'GNAME',
        'NOB',
        'DATE',
        'PLACE',
        'C1',
        'C2',
        'CY',
        'OCCU',
    ];
    
    /**
        Temporary file in data/tmp/gauq/g55/
        @param  $groupKey a key of G55::GROUPS, like '570SPO'
    **/
    public static function tmpFilename($groupKey){
        return implode(DS, [Config::$data['dirs']['tmp'], 'gauq', 'g55', $groupKey . '.csv']);
    }
    
    /**
        Returns the name of a tmp file in data/tmp/gauq/g55/
        Each element contains the person fields in an assoc. array.
    **/
    public static function loadTmpFile($groupKey){
        return csvAssociative::compute(self::tmpFilename($groupKey), G5::CSV_SEP);
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
    
    /** 
        Matching between G55 records and LERRCP.
        Format, for each G55 file: NUM in G55 file => GQID (LERRCP id).
        Array built from results of command gqid check.
    **/
    const MATCH_LERRCP = [
        '361PEI' => [
            '181' => 'A6-355', // gautier-theophile
            '258' => 'E3-936', // le-molt-philippe
            '340' => 'A6-689', // raimbaud-arthur
        ],
        '513PRE' => [
            '124' => 'A2-2628', // colin-henri-1880-11-01
        ],
    ];

} // end class    

