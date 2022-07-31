<?php
/********************************************************************************
    Code related to the inclusion of data contained in Gauquelin 1955 book "L'influence des astres"
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-05-08 23:39:19+02:00, Thierry Graff : creation
    @history    2019-04-08 15:24:04+02:00, Thierry Graff : Start generation of 2 versions : original and corrected
    @history    2022-05-25 22:53:23+02:00, Thierry Graff : New version, to start inclusion of minor painters and priests
    @history    2022-07-27 08:07:23+02:00, Thierry Graff : Refactor to include all groups
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
        @param $N           Value of field NUM of a record within the group ( = record number, starting from 1).
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
        Association group key => characteristics of 1955 groups
        Conventions :
            - Main groups and subgroups are given an attribute called their "order", built as follows:
                - Main groups are numbered from 01 to 10, in the order they appear in the book.
                - Subgroups are numbered using the order of their parent group, followed by a letter - ex 04a, 04b
            - Main groups and subgroups are identified by a "group key"
            A group key is composed by
                - the group's order
                - the group's number of elements, as stated in the book
                - a string describing the group content.
                ex: '01-576-physicians'
                
            With these definitions, 
            - Group keys are used to build the names of the different files.
                - raw files in data/raw/gauq/g55/
                - group definitions in data/db/group/gauq/g55/
                - tmp files in data/tmp/gauq/g55/
            - Group slugs are built from group keys, replacing the order by the string 'g55'. ex: 'g55-576-physicians'
            
    **/
    const GROUPS = [

        '01-576-physicians' => [
            'title' => "576 membres associés et correspondants de l'académie de médecine",
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '02-508-physicians' => [
            'title' => '508 autres médecins notables',
            'lerrcp' => 'A2',
            'occupation' => 'physicist',
        ],
        '03-570-sportsmen' => [
            'title' => '570 sportifs',
            'lerrcp' => 'A1',
            'occupation' => 'sportsperson',
        ],
        '04-676-military' => [
            'title' => '676 militaires',
            'lerrcp' => 'A3',
            'occupation' => 'military-personnel',
            'children' => [
                '04a-596-officiers-superieurs',
                '04b-81-saint-cyriens',
            ],
        ],
        '05-906-painters' => [
            'title' => '906 peintres',
            'lerrcp' => 'A4',
            'occupation' => 'painter',
            'children' => [
                '05a-237-peintres-celebres',
                '05b-668-peintres-notables',
            ],
        ],
        '06-361-minor-painters' => [
            'title' => '361 peintres mineurs',
            //no lerrcp
            'occupation' => 'painter',
        ],
        '07-500-actors' => [
            'title' => '500 acteurs',
            'lerrcp' => 'A5',
            'occupation' => 'actor',
            'children' => [
                '07a-122-acteurs-celebres-du-siecle-dernier',
                '07b-225-acteurs-contemporains-celebres',
                '07c-153-acteurs-contemporains-moins-connus',
            ],
        ],
        '08-494-deputies' => [
            'title' => '494 députés',
            'lerrcp' => 'A5',
            'occupation' => 'politician',
            'children' => [
                '08a-135-deputes-connus',
                '08b-359-deputes-moins-connus',
            ],
        ],
        '09-349-scientists' => [
            'title' => "349 membres associés et correspondants de l'académie des sciences",
            'lerrcp' => 'A2',
            'occupation' => 'scientist',
        ],
        '10-884-priests' => [
            'title' => '884 prêtres',
            //no lerrcp
            'occupation' => 'catholic-priest',
            'children' => [
                '10a-513-priests-paris',
                '10b-369-priests-albi',
            ],
        ],
    ];
    
    /** 
        Matching between G55 records and LERRCP.
        Format, for each G55 file: NUM in G55 file => GQID (LERRCP id).
        'none' means that there is no corresponding person in LERRCP
        Array built from results of command gqid check.
    **/
    const MATCH_LERRCP = [
        '01-576-physicians' => [
            '6'     => 'A2-9',      // ambart-leon-1876-12-16
            '22'    => 'A2-27',     // baillet-louis-rene-1834-09-07
            '28'    => 'A2-35',     // barbary-jean-baptiste-1867-06-16
            '47'    => 'A2-58',     // berard-leon-eugene-1870-02-17
            '50'    => 'A2-61',     // bergonie-jean-alban-1857-10-07
            '88'    => 'none',      // brown-sequard-edouard-1817-04-17
            '90'    => 'A2-116',    // bucquoy-marie-jules-1829-08-14
            '91'    => 'A2-117',    // buignet-henri-1815-03-02
            '112'   => 'A2-144',    // charcellay-laplace-jules-1809-10-31
            '113'   => 'A2-145',    // charcot-jean-baptiste-1867-07-15
            '137'   => 'A2-175',    // coste-jean-1807-05-10
            '143'   => 'A2-186',    // cuneo-bernard-1873-10-28
            '182'   => 'A2-237',    // duclos-michel-1822-12-16
            '183'   => 'A2-238',    // duguet-jean-baptiste-1837-05-12
            '188'   => 'A2-245',    // dupuy-pierre-1844-04-12
            '199'   => 'A2-262',    // favre-pierre-1813-03-20
            '207'   => 'A2-271',    // florence-albert-1851-04-23
            '213'   => 'A2-278',    // fontan-jules-1849-10-22
            '224'   => 'A2-292',    // gaudier-henri-1866-08-06
            '229'   => 'A2-297',    // gerdy-joseph-vulf-1809-03-20
            '232'   => 'A2-300',    // gilis-jean-louis-1857-01-25
            '239'   => 'A2-311',    // gosset-antonin-1872-01-21
            '250'   => 'A2-322',    // guerin-alphonse-1816-04-09
            '267'   => 'A2-345',    // hervieux-jacques-1818-09-04
            '270'   => 'A2-349',    // huchard-henri-1844-04-05
            '272'   => 'A2-351',    // huguier-pierre-1804-09-04
            '277'   => 'A2-357',    // jacquemin-eugene-1828-01-23
            '278'   => 'A2-356',    // jacquemier-jean-marie-1806-01-16
            '285'   => 'A2-366',    // jolyet-felix-1841-01-10
            '294'   => 'A2-377',    // laboulbene-alexandre-1825-08-24
            '306'   => 'A2-395',    // lannois-maurice-1856-11-08
            '337'   => 'A2-438',    // levieux-jean-1818-11-23
            '357'   => 'A2-469',    // manquat-alexandre-1858-12-02
            '372'   => 'A2-486',    // mathis-constant-jean-1871-09-19
            '379'   => 'none',      // melier-francois
            '382'   => 'none',      // merget-antoine
            '414'   => 'A2-540',    // notta-alphonse-1824-02-27
        ],
        '06-361-minor-painters' => [
            '181'   => 'A6-355',    // gautier-theophile
            '258'   => 'E3-936',    // le-molt-philippe
            '340'   => 'A6-689',    // raimbaud-arthur
        ],
        '10-884-priests' => [
            '493'   => 'A2-2628',   // colin-henri-1880-11-01
        ],
    ];

    /**
        Returns the possible group keys that can be used to invoke commands raw2tmp and tmp2db
    **/
    public static function getPossibleGroupKeys() {
        return array_keys(self::GROUPS);
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
        Path to a g55 raw file.
        @param  $groupKey a key of G55::GROUPS, like '09-349-scientists'
        throws  Exception if raw file not defined for this group
    **/
    public static function rawFilename(string $groupKey): string {
        return implode(DS, [Config::$data['dirs']['raw'], 'gauq', 'g55', $groupKey . '.txt']);
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
        'OTHER',
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
    
} // end class

