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
        Returns a unique Gauquelin 1955 id, like "01-123"
        Unique id of a record among birth dates published in Gauquelin's 1955 book.
        See https://tig12.github.io/gauquelin5/g55.html for precise definition.
        @param $groupKey    String like '570SPO', one of the key of G55:GROUPS
        @param $N           Value of field NUM of a record within the group ( = record number, starting from 1).
    **/
    public static function g55Id($groupKey, $N){
        return substr($groupKey, 0, 2) . "-$N";
    }
    
    
    // *********************** Source management ***********************
    // When Gauquelin 1955 book is considered as an information source.
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'gauq' . DS . 'g55-book.yml';
    
    /** Slug of source  **/
    const SOURCE_SLUG = 'g55-book';
    
    
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
            - Group slugs are built from group keys, appending the string 'g55-'. ex: 'g55-01-576-physicians'
            
    **/
    const GROUPS = [
        '01-576-physicians' => [
            'occupation' => 'physician',
        ],
        '02-508-physicians' => [
            'occupation' => 'physician',
        ],
        '03-570-sportsmen' => [
            'occupation' => 'sportsperson',
        ],
        '04-676-military' => [
            'occupation' => 'military-personnel',
            'children' => [
                '04a-596-officiers-superieurs',
                '04b-81-saint-cyriens',
            ],
        ],
        '05-906-painters' => [
            'occupation' => 'painter',
            'children' => [
                '05a-237-peintres-celebres',
                '05b-668-peintres-notables',
            ],
        ],
        '06-361-minor-painters' => [
            'occupation' => 'painter',
        ],
        '07-500-actors' => [
            'occupation' => 'actor',
            'children' => [
                '07a-122-acteurs-celebres-du-siecle-dernier',
                '07b-225-acteurs-contemporains-celebres',
                '07c-153-acteurs-contemporains-moins-connus',
            ],
        ],
        '08-494-deputies' => [
            'occupation' => 'politician',
            'children' => [
                '08a-135-deputes-connus',
                '08b-359-deputes-moins-connus',
            ],
        ],
        '09-349-scientists' => [
            'occupation' => 'scientist',
        ],
        '10-884-priests' => [
            'title' => '884 prêtres',
            'occupation' => 'catholic-priest',
            'children' => [
                '10a-513-priests-paris',
                '10b-369-priests-albi',
            ],
        ],
    ];
    
    /**
        Returns the possible group keys that can be used to invoke commands raw2tmp and tmp2db
    **/
    public static function getPossibleGroupKeys() {
        return array_keys(self::GROUPS);
    }
    
    /**
        Computes a group slug from a group key.
        Implements a convention described in doc comment of self::GROUPS
    **/
    public static function groupKey2slug($groupKey) {
        return 'g55-' . $groupKey;
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
            '373'   => 'A2-488',    // maunoury-victor-gabriel-1850-10-05
            '379'   => 'none',      // melier-francois
            '382'   => 'none',      // merget-antoine
            '383'   => 'A2-499',    // merklen-prosper-1874-04-25
            '401'   => 'A2-521',    // motet-auguste-1832-09-06
            '414'   => 'A2-540',    // notta-alphonse-1824-02-27
            '416'   => 'A2-542',    // ollier-leopold-1830-12-03
            '436'   => 'A2-569',    // perrin-maurice-1826-04-14
            '496'   => 'A2-651',    // routier-edmond-1873-10-10
            '500'   => 'A2-433',    // roy-des-barres-adrien-1872-12-16
            '512'   => 'A2-666',    // see-marc-1827-02-18
        ],
        '02-508-physicians' => [
            '6'     => 'A2-5',      // alajouanine-th-1890-06-42
            '73'    => 'A2-830',    // boucomont-roger-1902-10-19
            '74'    => 'A2-831',    // bouffe-de-saint-blaise-gabr-1862-10-02
            '100'   => 'A2-859',    // cambon-emile-m-l-1888-01-18
            '107'   => 'A2-867',    // carlier-paul-rene-1894-09-24
            '110'   => 'A2-869',    // carpentier-william-1887-01-01
            '155'   => 'A2-915',    // cournet-jean-fr-1863-01-24
            '158'   => 'A2-918',    // crehange-jean-louis-1899-12-12
            '194'   => 'A2-964',    // dumas-antoine-1882-02-24
            '206'   => 'A2-977',    // estradere-jean-etien-1900-02-49
            '208'   => 'A2-980',    // fabre-jean-roger-1894-07-26
            '211'   => 'A2-983',    // ferry-pierre-1871-02-05
            '249'   => 'A2-1027',   // grenier-de-cardenal-henri-1875-02-10
            '266'   => 'A2-1047',   // henry-jean-robert-1893-05-27
            '273'   => 'A2-1057',   // jaulin-du-seutre-m-auguste-1882-12-29
            '295'   => 'A2-1083',   // la-roche-de-brisson-rene-1883-11-21
            '342'   => 'A2-1132',   // martel-de-janville-thierry-1875-03-07
            '360'   => 'none',      // mitzer-1905-09-01 -- WARNING DUBIOUS RECORD
            '379'   => 'A2-1168',   // negre-leopold-1872-06-15
            '437'   => 'none',      // rome-alfred-1869-04-23
            '450'   => 'A2-1246',   // sauve-louis-de-gonzague-1881-04-26
            '459'   => 'A2-1261',   // sikora-pierre-1874-08-24
            '508'   => 'A2-1320',   // woringer-frederic-1890-08-26
        ],
        '06-361-minor-painters' => [
            '5'     => 'none',
            '9'     => 'none',
            '12'    => 'none',
            '20'    => 'none',
            '21'    => 'none',
            '24'    => 'none',
            '26'    => 'none',
            '27'    => 'none',
            '28'    => 'none',
            '29'    => 'none',
            '35'    => 'none',
            '36'    => 'none',
            '46'    => 'none',
            '52'    => 'none',
            '62'    => 'none',
            '69'    => 'none',
            '71'    => 'none',
            '72'    => 'none',
            '76'    => 'none',
            '82'    => 'none',
            '88'    => 'none',
            '89'    => 'none',
            '93'    => 'none',
            '94'    => 'none',
            '97'    => 'none',
            '98'    => 'none',
            '99'    => 'none',
            '106'   => 'none',
            '118'   => 'none',
            '122'   => 'none',
            '125'   => 'none',
            '126'   => 'none',
            '131'   => 'none',
            '133'   => 'none',
            '138'   => 'none',
            '140'   => 'none',
            '144'   => 'none',
            '150'   => 'none',
            '158'   => 'none',
            '173'   => 'none',
            '181'   => 'A6-355',    // gautier-theophile
            '197'   => 'none',
            '198'   => 'none',
            '200'   => 'none',
            '201'   => 'none',
            '204'   => 'none',
            '205'   => 'none',
            '207'   => 'none',
            '208'   => 'none',
            '209'   => 'none',
            '211'   => 'none',
            '218'   => 'none',
            '221'   => 'none',
            '223'   => 'none',
            '232'   => 'none',
            '234'   => 'none',
            '236'   => 'none',
            '247'   => 'none',
            '248'   => 'none',
            '250'   => 'none',
            '251'   => 'none',
            '253'   => 'none',
            '254'   => 'none',
            '256'   => 'none',
            '258'   => 'E3-936',    // le-molt-philippe
            '259'   => 'none',
            '271'   => 'none',
            '280'   => 'none',
            '285'   => 'none',
            '291'   => 'none',
            '293'   => 'none',
            '298'   => 'none',
            '299'   => 'none',
            '300'   => 'none',
            '301'   => 'none',
            '303'   => 'none',
            '306'   => 'none',
            '309'   => 'none',
            '313'   => 'none',
            '317'   => 'none',
            '318'   => 'none',
            '320'   => 'none',
            '324'   => 'none',
            '326'   => 'none',
            '329'   => 'none',
            '331'   => 'none',
            '340'   => 'A6-689',    // raimbaud-arthur
            '348'   => 'none',
            '359'   => 'none',
        ],
        '10-884-priests' => [
            '493'   => 'A2-2628',   // colin-henri-1880-11-01
        ],
    ];

} // end class

