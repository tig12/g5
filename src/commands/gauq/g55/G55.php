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
        '04-676-military' => [
            '9'   => 'A3-10', // adhemar-jean-pierre-1868-07-20
            '10'  => 'A3-11', // adorno-de-tscharner-anioine-1829-09-29
            '16'  => 'A3-17', // allehaut-emile-1872-05-03 07:30, La Rochelle, 17
            '20'  => 'A3-21', // altmayer-victor-joseph-1844-06-14 21:00, Saint-Avold, 57
            '21'  => 'A3-22', // amar-ernest-andre-1850-08-14 07:00, Guillestre, 05
            '27'  => 'A3-25', // ameil-alfred-frederic-1810-11-08 03:15, Saint-Omer, 62
            '29'  => 'A3-31', // andre-louis-joseph-1838-03-28 22:00, Nuits-Saint-Georges, 21
            '35'  => 'A3-39', // appert-henri-lawis-1851-12-04 16:30, Alger, 
            '39'  => 'A3-47', // arlabosse-emile-olivier-1857-10-30 21:00, Perpignan, 66
            '44'  => 'A3-52', // arnoux-paul-edouard-1822-02-19 08:00, Poitiers, 86
            '51'  => 'A3-63', // aubertin-claude-fabien-1844-03-27 01:00, Blondefontaine, 70
            '54'  => 'A3-66', // aubigny-georges-marie-1844-06-03 07:00, Saumur, 49
            '58'  => 'A3-69', // audouin-antoine-gontran-1874-11-12 17:30, Mouthiers, 16
            '61'  => 'A3-74', // aurelle-de-paladine-1804-01-14 11:00, Malziew, 48
            '63'  => 'A3-79', // avelot-rene-antoine-1871-12-01 14:30, Saint-Germain-en-Laye, 78
            '65'  => 'A3-80', // aviau-de-piolant-albert-charles-1845-10-28 13:00, Niort, 79
            '66'  => 'A3-81', // aymerich-joseph-gauder-1858-02-20 21:00, Estagel, 66
            '81'  => 'A3-100', // baquet-louis-henri-1858-06-25 10:00, Sedan, 08
            '83'  => 'A3-102', // barail-francois-charles-1820-05-28 23:00, Versailles, 78
            '86'  => 'A3-105', // barbancey-jean-marcel-1874-05-25 16:00, Villefranche-de-Lonchat, 24
            '88'  => 'A3-107', // barbassat-alfred-alexandre-1874-08-01 05:00, Grenoble, 38
            '98'  => 'A3-117', // baril-eugene-guillaume-1850-11-07 20:00, Nimes, 30
            '99'  => 'A3-120', // barrabe-michel-joseph-1821-04-16 04:00, Gorron, 53
            '109' => 'A3-131', // basire-marcel-1869-02-29 15:00, Dôle, 39
            '110' => 'A2-2563', // bassot-leon-pierre-1841-04-06 21:00, Renéve, 21
            '111' => 'A3-133', // baston-paul-marie-1863-03-28 01:00, Saint-Claude, 39
            '114' => 'A3-136', // battesti-jules-augustin-1858-04-06 10:00, Gravelines, 59
            '118' => 'A3-141', // baudic-joseph-louis-1845-10-28 10:00, Lorient, 56
            '131' => 'A3-157', // beaugier-francois-alfred-1842-04-18 19:00, Le Puy, 43
            '135' => 'A3-161', // bedeau-marie-alphonse-1804-08-04 12:00, Vertou, 44
            '136' => 'A3-163', // bedoin-emile-joseph-1821-01-03 06:00, Romans-sur-Isére, 26
            '138' => 'A3-165', // behic-charles-francois-1826-06-22 01:30, Morlaix, 29
            '141' => 'A3-170', // benic-francois-colomba-1816-01-23 16:00, Saint-Pere, 35
            '153' => 'A3-182', // berge-paul-louis-1860-02-06 22:30, Reims, 51
            '154' => 'A3-183', // berger-paul-charles-1880-06-29 02:00, Montbeliard, 25
            '155' => 'A3-185', // berges-michel-albert-1894-01-04 17:00, Pau, 64
            '157' => 'A3-187', // bernard-frederic-charles-1851-12-21 22:00, Arras, 62
            '160' => 'A3-191', // bernard-jules-pierre-1876-08-19 17:00, Pernes, 84
            '162' => 'A3-193', // bernis-francois-justin-1814-10-07 18:00, Nimes, 30
            '163' => 'A3-194', // berruyer-ulysse-louis-1836-02-21 05:30, Ploérmel, 56
            '167' => 'A3-198', // berthelot-henri-mathias-1861-12-07 07:00, Feurs, 42
            '180' => 'A3-211', // bessiere-jean-pierre-1880-08-10 06:00, Beauvais, 60
            '181' => 'A3-212', // besson-joseph-pierre-1843-06-16 03:00, Bourg-sur-Gironde, 33
            '183' => 'A3-215', // betrix-jean-joseph-1867-06-16 20:00, Annecy, 74
            '184' => 'A3-216', // bettembourg-adolphe-henri-1882-03-04 03:00, St-Bazeille, 47
            '187' => 'A3-219', // beziat-helvi-theophile-1823-01-03 05:00, Saussenac, 81
            '194' => 'A3-228', // bigrel-theophile-hyac-1828-04-29 17:00, Loudéac, 22
            '196' => 'A3-230', // billotte-gaston-henri-1875-02-10 08:00, Sommeval, 10
            '200' => 'A3-235', // birot-jean-pierre-jos-1868-11-08 20:00, Saint-Affrique, 12
            '208' => 'A3-247', // blanche-ferdinand-marie-1848-09-27 18:00, Redon, 35
            '216' => 'A3-256', // blois-etienne-gabriel-1801-10-14 04:00, Ploujean, 29
            '223' => 'A3-265', // boell-marie-joseph-1849-01-17 17:00, Villé, 67
            '224' => 'A3-266', // boelle-victor-rene-1850-05-10 12:00, Brest, 29
            '227' => 'A3-269', // boigues-paul-marie-1864-05-27 03:30, Le Havre, 76
            '228' => 'A3-270', // boileve-charles-emile-1837-12-06 16:00, Le Château-d'Oléron, 17
            '235' => 'A3-277', // boissau-robert-marie-1886-07-09 07:00, Montlignon, 95
            '236' => 'A3-278', // boisse-emile-jean-1848-01-14 10:30, Blaye, 81
            '238' => 'A3-281', // boissin-mathieu-rene-1872-05-03 13:00, Montlucon, 03
            '240' => 'A3-283', // boistertre-jean-anatole-1810-09-20 08:00, Notre-Dame du Hamel, 27
            '248' => 'A3-293', // bongarcon-alphonse-camille-1834-02-07 02:00, Sisteron, 04
            '251' => 'A3-297', // bonnal-guillaume-1844-03-27 06:00, Toulouse, 31
            '255' => 'A3-301', // bonnet-aristide-michel-1839-08-09 10:00, Besancon, 25
            '256' => 'A3-302', // bonnet-bruneau-ferdin-1864-03-14 01:00, Siran, 34
            '259' => 'A3-305', // bonneval-abriat-de-laforest-1829-06-07 01:00, Limoges, 89
            '262' => 'A3-309', // bonviolle-charles-henry-1867-09-06 16:00, Metz, 57
            '265' => 'none', // bordas-antoine-emmanuel-1809-10-23 11:00, Orléans, 45
            '268' => 'A3-315', // borel-jean-louis-1819-04-03 13:00, Fangeaux, 11
            '270' => 'A3-317', // borgnis-desbordes-charles-ern-1843-05-17 23:00, Provins, 77
            '272' => 'A3-319', // borius-leon-charles-1835-06-23 18:00, Rochefort-sur-Mer, 17
            '273' => 'A3-321', // borschneck-charles-emile-1871-03-16 01:00, Bischwiller, 67
            '282' => 'A3-333', // bouchard-raoul-paul-1851-08-09 09:00, Metz, 57
            '284' => 'A3-335', // boucher-eugene-arthur-1847-85-19 16:00, Sully-sur-Loire, 45
            '293' => 'A3-346', // bouet-alexandre-eugene-1833-12-06 14:30, Bayonne, 64
            '296' => 'A3-354', // bourgeois-joseph-emile-1857-02-21 23:00, Sainte-Marie-aux-Mines, 68
            '298' => 'A3-362', // brecard-charles-theodore-1867-10-14 19:00, Sidi-Bel-Abbès, 
            '300' => 'A3-369', // brissot-desmaillet-georges-1869-01-16 17:30, Carcassonne, 11
            '307' => 'A3-383', // calmel-jean-bernard-1865-05-10 15:00, Toulouse, 31
            '313' => 'A3-399', // catroux-georges-albert-1877-01-29 21:00, Limoges, 89
            '324' => 'A3-420', // chretien-adrien-paul-1862-09-12 414:00, Auxonne, 21
            '336' => 'A3-449', // cugnac-jean-gaspard-1861-04-10 10:00, Epannes, 79
            '338' => 'A3-457', // dauve-pierre-camille-1863-14-09 12:00, Blida, 
            '339' => 'A3-459', // debeney-marie-eugene-1864-05-09 05:30, Bourg, 01
            '350' => 'A3-487', // driant-emile-auguste-1855-09-11 08:30, Neufchatel, 02
            '352' => 'A3-493', // dubois-emile-oscar-1842-05-12 06:00, Hermonville, 51
            '353' => 'A3-496', // duchene-denis-auguste-1862-09-23 01:00, Juzennecourt, 52
            '356' => 'A3-499', // dufieux-julien-claude-1873-05-21 01:00, Mascara, 
            '360' => 'A3-506', // dupont-charles-joseph-1863-10-30 12:00, Nancy, 54
            '361' => 'A3-507', // duport-georges-pierre-1864-02-04 08:00, Haguenau, 67
            '366' => 'A3-519', // exelmans-charles-marie-1854-07-20 04:00, Lyon, 69
            '367' => 'A3-521', // faidherbe-louis-1818-06-03 00:00, Lille, 59
            '376' => 'A3-533', // feraud-eugene-j-b-1862-01-28 15:00, Constantine, 
            '382' => 'A3-542', // forgemol-de-bostquenard-leon-1821-09-17 04:00, Azerables, 23
            '384' => 'A3-546', // fournier-francois-ernest-1842-05-23 04:00, Toulouse, 31
            '386' => 'A3-549', // fracque-charles-celien-1875-02-01 18:00, Saint-Claude, 39
            '390' => 'A2-2697', // gallieni-joseph-simon-1849-04-24 05:00, Saint-Béat, 31
            '398' => 'A3-586', // grandclement-raoul-1866-10-08 09:30, Brest, 29
            '401' => 'A3-590', // graziani-jean-cesar-1859-11-15 21:00, Bastia, 20
            '404' => 'A3-600', // guillaumat-marie-louis-1863-01-04 14:00, Bourgneuf, 17
            '414' => 'A3-629', // humbert-georges-louis-1862-04-08 19:00, Gazeran, 78
            '416' => 'A3-642', // joba-marie-joseph-h-1864-07-02 20:30, Commercy, 55
            '417' => 'A3-643', // joffre-joseph-jacques-1852-01-12 10:00, Rivesaltes, 66
            '418' => 'A3-650', // juin-alphonse-pierre-1888-12-16 11:00, Bône, 
            '420' => 'A3-654', // koenig-marie-pierre-1898-10-10 23:50, Caen, 14
            '422' => 'A3-658', // lacapelle-gustave-paul-1869-10-09 14:00, Troyes, 10
            '423' => 'A3-659', // lacaze-marie-jean-1860-06-22 04:00, Pierrefond, 60
            '426' => 'A3-669', // lagrue-eugene-georges-1871-07-22 05:00, Fécamp, 76
            '432' => 'A3-677', // langlois-hippolyte-1839-08-03 15:00, Besancon, 25
            '434' => 'A3-688', // lattre-de-tassigny-jean-marie-1889-02-02 12:00, Mouilleron-en-Pareds, 85
            '436' => 'A3-692', // laurent-victor-simon-1862-09-21 12:00, Serrigny, 21
            '437' => 'A3-700', // lebouc-georges-pierre-1865-10-03 01:00, Issoudun, 36
            '440' => 'A3-707', // lecointe-alphonse-theodore-1817-07-12 22:00, Evreux, 27
            '449' => 'A3-739', // lucas-henri-pascal-1874-04-13 15:30, Montfort-l’Amaury, 78
            '451' => 'A3-741', // mac-mahon-marie-edme-1805-06-13 12:00, Sully, 71
            '456' => 'A3-749', // mangin-joseph-emile-1867-38-19 07:00, Ars-en-Moselle, 57
            '458' => 'A3-752', // marchand-jean-baptiste-1863-11-22 20:00, Thoissey, 01
            '468' => 'A3-770', // maunoury-michel-joseph-1847-12-17 02:00, ’ Maintenon, 28
            '469' => 'A3-771', // maurin-louis-felix-1869-01-05 11:00, Cherbourg, 50
            '475' => 'A3-785', // metz-adalbert-francois-1867-04-17 10:00, Beauvais, 60
            '476' => 'A3-786', // metz-pierre-marie-1874-10-26 08:00, Saini-Max, 54
            '477' => 'A3-788', // meurisse-georges-1873-11-22 10:00, Tarbes, 65
            '483' => 'A3-791', // michelin-pierre-1876-11-49 01:00, Commenailles, 39
            '487' => 'A3-797', // mittelhausser-eugene-desire-1873-08-07 13:00, Tourcoing, 59
            '490' => 'A3-803', // monroe-marie-louis-1862-10-02 02:00, Perreux, 42
            '492' => 'A3-806', // mordrelle-joseph-jean-m-1863-09-17 19:40, Hédé, 37
            '493' => 'A3-813', // mouchard-francois-1883-10-04 10:00, Toulouse, 31
            '494' => 'A3-814', // mouchon-emile-hippolyte-1865-02-04 12:00, Saint-Hippolyte-du-Fort, 30
            '499' => 'A3-825', // negrier-francois-oscar-1839-10-02 09:00, Belfort, 90
            '503' => 'A3-839', // olry-rene-henri-1880-06-28 08:00, Lille, 59
            '514' => 'A3-855', // pelle-maurice-cesar-1863-04-18 02:30, Douai, 59
            '523' => 'A3-872', // philippe-albert-marie-1863-02-13 19:00, Delouze, 55
            '543' => 'A1-187', // sadi-lecointe-1891-07-11 23:30, St-Germain-sur-Bresle, 80
            '545' => 'A3-945', // saussier-felix-gustave-1828-01-16 17:00, Troyes, 10
            '547' => 'A3-948', // schlumberger-charles-robert-1859-05-28 22:30, Nancy, 54
            '548' => 'A2-2886', // sebert-hippolyte-1839-01-31 12:00, Verberie, 60
            '550' => 'A3-954', // serot-almeras-latour-augustin-1868-05-12 16:30, Rethel, 08
            '568' => 'A3-986', // toutee-georges-joseph-1855-02-20 11:00 Saint-Fargeau
            '573' => 'A3-997', // valagregue-georges-1852-09-20 16:00, Carpentras, 84
            '575' => 'A3-1000', // vasselot-de-regne-1888-09-15 14:00, La Guillotiére, 79
            '576' => 'A3-1001', // vaulgrenant-albert-1872-04-08 13 13:00, Versailles, 78
            '579' => 'A3-1005', // verdier-pierre-bernard-1875-11-20 17:00, Loures, 65
            '580' => 'A3-1006', // vergnette-de-lamotte-1877-09-17 05:00, Montpellier, 34
            '587' => 'A3-1021', // viviez-pierre-jean-b-1877-08-06 20:10, Pau, 64
            '588' => 'A3-1023', // voisin-andre-1877-03-02 01:00, Nancy, 54
            '599' => 'A3-86',  // bahier-victor-1916-08-05 01:00, Saint-Brieuc, 22
            '604' => 'A3-261', // bloy-jean-jacques-1917-09-11 21:00, Lodéve, 34
            '618' => 'A3-513', // engel-pierre-1917-09-11 04:00, Chambéry, 73
            '622' => 'A3-583', // goupil-henri-1917-11-19 05:00, La Roche-sur-Yon, 85
            '632' => 'A3-664', // lafargue-jean-1917-11-19 10:00, Navarrenx, 64
            '625' => 'A3-605', // guipet-rene-paul-jean-b-1918-06-24 01:00, Mesnay, 39
            '626' => 'A3-626', // huber-paul-1916-12-11 15:30, Strasbourg, 67
            '628' => 'A3-634', // jacquet-norbert-paul-1915-07-17 09:00, Macon, 71
            '630' => 'A3-647', // jorna-leon-1916-09-23 04:00, Chamonix, 74
            '646' => 'A3-779', // menard-joseph-1916-09-23 15:00, Nantes, 44
            '631' => 'A3-661', // lacourt-jacques-georges-1917-08-09 10:00, Fére-en-Tardenois, 02
            '651' => 'A3-841', // oudet-paul-1917-08-09 01:00, Saintes, 17
            '633' => 'A3-678', // langlois-pierre-1918-05-05 0O:30, Brest, 29
            '637' => 'A3-697', // le-berre-yves-louis-1915-04-28 23:00, Quimper, 29
            '642' => 'A3-723', // leoutre-marcel-louis-1917-06-15 08:00, Thomery, 77
            '654' => 'A3-858', // penfentenyo-de-kervereguen-fran-1915-05-08 21:35, Brest, 29
            '657' => 'A3-875', // pietri-alex-1918-05-04 09:00, Sétif, 
            '670' => 'none',   // sicard-andre-1916-07-28 04:00, Marseille, 13
            '675' => 'A3-1010', // vidalin-maurice-1918-10-20 18:00, Oran, 
            '677' => 'A3-1031', // wisdorff-bernard-1917-10-10 08:15, Neuilly-sur-Seine, 92
        ],
        '05-906-painters' => [
            '20'  => 'A4-97',  // berne-bellecour-etienne-pr-1838-07-29 17:00, Boulogne-sur-Mer, 62
            '36'  => 'A4-159', // braque-georges 1882-05-09 02:30 Argenteuil
            '46'  => 'A4-176', // cabanel-alexandre-1824-09-28 00:00, Montpellier, 34
            '62'  => 'A4-227', // chintreuil-antoine-1815-05-15 08:00, Pont-de-Vaux, 01
            '71'  => 'A4-257', // couturier-philibert-leon-1823-05-26 23:00, Chalon-sur-Saône, 71
            '75'  => 'A4-274', // debat-ponsan-edouard-1847-04-25 10:00, Toulouse, 31
            '83'  => 'A4-320', // diaz-de-la-pena-narcisse-virg-1807-08-20 08:00, Bordeaux, 33
            '88'  => 'A4-330', // dourouze-daniel-urbain-1874-03-21 08:00, Grenoble, 38
            '100' => 'A4-374', // d-espagnat-georges-1870-08-14 23:00, Melun, 77
            '107' => 'A4-400', // flandrin-hippolyte-1809-03-23 15:00, Lyon, 69
            '113' => 'A4-583', // fresnaye-roger-1885-07-11 15:45, Le Mans, 72
            '115' => 'A4-423', // friesz-othon-1879-02-06 13:00, Le Havre, 76
            '211' => 'A4-907', // poulbot-francisque-1879-02-06 09:00, Saint-Denis, 93
            '117' => 'A4-428', // gagliardini-julien-gustave-1846-03-01 02:30, Mulhouse, 68
            '118' => 'A4-446', // genin-lucien-1894-11-09 01:00, Rouen, 76
            '131' => 'A4-501', // guignard-alexandre-gast-1848-03-08 14:00, Bordeaux, 33
            '133' => 'A4-507', // guirand-de-scevola-victor-1871-11-14 15:00, Sete, 34
            '137' => 'A4-517', // helleu-paul-cesar-1859-12-17 13:00, Vannes, 56
            '139' => 'A4-521', // herbin-auguste-1882-04-29 23:00, Quévy, 59
            '164' => 'A4-660', // leprin-marcel-1891-02-12 9:00, Cannes, 06
            '172' => 'A4-696', // maignan-albert-pierre-1845-10-14 18:00, Beaumont-sur-Sarthe, 72
            '174' => 'A4-700', // mainssieux-lucien-1885-08-04 16:00, Voiron, 38
            '177' => 'A4-714', // marcke-de-lummen-emile-van-1827-08-20 09:00, Sèvres, 92
            '178' => 'A4-722', // marilhat-prosper-georg-1811-02-22 06:00, Vertaizon, 63
            '185' => 'A4-752', // meissonier-jean-louis-e-1815-02-21 05:00, Lyon, 69
            '199' => 'A4-826', // olive-jean-baptiste-1848-07-30 03:00, Marseille, 13
            '200' => 'A4-832', // ottmann-henry-1877-04-10 13:00, Ancenis, 44
            '220' => 'A4-947', // renouart-charles-paul-1845-11-05 02:00, Cour-Cheverny, 41
            '355' => 'A4-170', // brunet-debaines-louis-alfred-1845-11-05 21:00, Le Havre, 76
            '222' => 'A4-951', // ricard-louis-gustave-1823-09-01 19:00, Marseille, 13
            '230' => 'A4-990', // roybet-ferdinand-1840-04-12 03:00, Uzés, 30
            '232' => 'A4-999', // sain-paul-jean-marie-1853-12-06 00:00, Avignon, 84
            '242' => 'A4-9', // aillet-edgar-adrien-1883-03-05 05:00, Eauze, 32
            '243' => 'A4-11', // alaux-marie-francois-1878-10-11 03:00, Bordeaux, 33
            '255' => 'A4-29', // astruc-zacharie-1833-02-20 02:00, Angers, 49
            '739' => 'A4-800', // moulinet-antoine-ed-1833-02-20 03:00, Olonzac-Minervois, 34
            '266' => 'A4-45', // oot-adrienne-elodie-1888-03-16 14:15, Montgeron, 91
            '274' => 'A4-46', // ballue-pierre-ernest-1855-02-27 13:00, La Haye-Descartes, 37
            '680' => 'A4-692', // magne-desire-alfred-1855-02-27 09:30, Lusignan, 86
            '278' => 'A4-58', // bastet-tancrede-jean-1858-04-16 . 13:00, Domène, 38
            '290' => 'A4-75', // beaussier-emile-1874-12-30 06:00, Avignon, 84
            '295' => 'A4-81', // bellee-le-goasbe-de-leon-1844-07-07 05:00, Ploérmel, 56
            '298' => 'A4-84', // benner-emmanuel-1836-03-28 19:30, Mulhouse, 68
            '299' => 'A4-85', // benner-jean-1836-03-28 19:00, Mulhouse, 68
            '300' => 'A4-87', // benvignat-charles-cesar-1805-12-24 00:00, Boulogne-sur-Mer, 62
            '308' => 'A4-98', // berne-bellecour-jean-jacques-1874-08-14 04:00, Saint-Germain-en-Laye, 78
            '321' => 'A4-115', // biessy-marie-gabriel-1854-03-25 07:00, Saint-Pierre-du-Mont, 40
            '333' => 'A4-129', // blanc-celestin-joseph-1817-11-22 10:00, Clelles, 38
            '364' => 'A4-189', // caron-henri-paul-edmond-1860-05-09 06:15, Abbeville, 80
            '381' => 'A4-210', // chapoton-gregoire-1845-12-20 23:00, Saint-Rambert-sur-Loire, 42
            '391' => 'A4-230', // chudant-jean-adolphe-1860-01-05 01:00, Besançon, 25
            '404' => 'A4-251', // cot-pierre-auguste-1837-02-17 12:00, Bédarieux, 34
            '407' => 'A4-259', // crapelet-louis-amable-1822-06-01 12:00, Auxerre, 89
            '421' => 'A4-278', // decisy-eugene-1866-02-06 19:00, Metz, 57
            '439' => 'A4-306', // desjobert-louis-remy-1817-04-16 04:00, Chateauroux, 36
            '447' => 'A4-314', // deve-eugene-1826-09-22 24:00, Rouen, 76
            '491' => 'A4-392', // feron-julien-hippol-1864-09-14 07:30, Saint-Jean-du-Cardonnay, 76
            '504' => 'A4-416', // fragnay-francois-1824-07-05 21:00, Pérouges, 01
            '507' => 'A4-427', // gabriel-fournier-francisque-1893-05-26 17:00, Grenoble, 38
            '510' => 'A4-433', // gallier-achille-gratien-1814-06-06 00:00, Bayonne, 64
            '512' => 'A4-435', // gandon-pierre-1899-07-20 18:00, L’Hay-les-Roses, 78
            '536' => 'A4-481', // grandsire-pierre-eugene-1825-03-18 16:00, Orléans, 45
            '543' => 'A4-490', // grivolas-pierre-1823-09-02 00:00, Avignon, 84
            '544' => 'A4-494', // gros-lucien-alphonse-1845-05-19 07:30, Wesserling, 68
            '548' => 'A4-502', // guignet-jean-adrien-1816-01-21 02:00, Annecy, 74
            '554' => 'A4-511', // hanoteau-hector-charles-1823-05-26 21:00, Decize, 58
            '556' => 'A4-513', // hans-waltz-jean-j-1873-02-23 13:00, Colmar, 68
            '558' => 'A4-518', // hellouin-xenophon-1820-02-20 08:00, Aunay-sur-Odon, 14
            '569' => 'A4-534', // huen-victor-1874-03-21 15:00, Colmar, 68
            '576' => 'A4-548', // jalabert-charles-frang-1819-01-01 17:00, Nimes, 30
            '587' => 'A4-561', // jourdain-roger-joseph-1845-12-11 06:00, Louviers, 27
            '588' => 'A4-562', // jourdan-louis-jean-1872-03-07 19:00, Bourg, 01
            '616' => 'A4-603', // laronze-jean-1852-11-25 05:00, Génelard, 71
            '624' => 'A4-615', // laurent-desrousseaux-henri-a-1862-07-15 15:00, Joinville-le-Pont, 94
            '640' => 'A4-647', // lemarie-des-landelles-emile-1847-01-14 00:00, Pontorson, 50
            '641' => 'A4-648', // lematte-jacques-franc-1850-07-26 0O:00, Saint-Quentin, 02
            '655' => 'A4-669', // lesrel-adolphe-alexan-1839-05-19 23:00, Genêts, 50
            '663' => 'A4-683', // lopisgich-antonio-georges-1854-03-29 18-:00, Vichy, 03
            '667' => 'A4-695', // mahe-edmond-francois-1905-05-01 03:00, Rennes, 35
            '676' => 'A4-713', // marche-ernest-gaston-1864-09-14 03:00, Nemours, 77
            '687' => 'A4-721', // marie-adrien-emmanu-1848-10-19 13:00, Neuilly-sur-Seine, 92
            '688' => 'A4-745', // maurin-charles-1856-04-01 09:00, Le Puy, 43
            '696' => 'A4-733', // marvy-louis-gerv-1815-05-15 05:00, Jouy-en-Josas, 78
            '704' => 'A4-746', // maury-georges-sauv-1872-10-06 01:00, Saint-Denis, 93
            '708' => 'A4-753', // melingue-etienne-mari-1808-04-16 03:00, Caen, 14
            '721' => 'A4-774', // milliet-jean-paul-1844-03-06 07:30, Le Mans, 72
            '724' => 'A4-776', // monchablon-xavier-alph-1835-06-12 14:00, Avillers, 88
            '733' => 'A4-790', // moreaux-charles-flor-1815-03-07 02:00, Rocroy, 08
            '750' => 'A4-814', // navlet-victor-1819-11-08 07:00, Châlons-sur-Marne, 51
            '766' => 'A4-837', // pabst-camille-alfred-1828-06-18 02:00, Heiteren, 68
            '802' => 'A4-882', // pierre-gustave-1875-03-07 23:00, Verdun, 55
            '806' => 'A4-888', // pinel-gustave-1842-04-15 14:00, Les Riceys, 10
            '823' => 'A4-914', // pron-louis-1817-12-19 17:00, Sézanne, 51
            '824' => 'A4-916', // prouve-victor-1858-08-13 19:00, Nancy, 54
            '825' => 'A4-920', // quentin-bernard-1923-06-02 09:00, Flamicourt, 80
            '836' => 'A4-935', // raverat-vincent-1801-01-22 10:00, Moutier-Saint-Jean, 21
            '852' => 'A4-961', // robinet-gustave-1845-04-11 05:00, Magny-Vernois, 72
            '854' => 'A4-963', // rochebrune-octave-1824-04-01 02:00, Fontenay-le-Comte, 85
            '858' => 'A4-969', // ronot-charles-1820-05-28 01:00, Belan-sur-Ource, 21
            '878' => 'A4-1036', // saintin-jules-1829-08-14 18:00, Lemé, 02
            '882' => 'A4-1005', // salome-louis-1833-12-17 12:00, Lille, 59
            '900' => 'A4-1027', // sem-joseph-goursat-1863-11-22 21:00, Périgueux, 24
            '902' => 'A4-1029', // serret-charles-1824-07-05 07:00, Aubenas, 07
        ],
        // groups not published in LERRCP booklets
        // need to avoid false matching (same birth day)
        // => lots of none
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
            '4'     => 'none',
            '5'     => 'none',
            '7'     => 'none',
            '10'    => 'none',
            '13'    => 'none',
            '16'    => 'none',
            '22'    => 'none',
            '24'    => 'none',
            '26'    => 'none',
            '29'    => 'none',
            '30'    => 'none',
            '36'    => 'none',
            '39'    => 'none',
            '40'    => 'none',
            '42'    => 'none',
            '45'    => 'none',
            '46'    => 'none',
            '50'    => 'none',
            '54'    => 'none',
            '58'    => 'none',
            '60'    => 'none',
            '61'    => 'none',
            '64'    => 'none',
            '66'    => 'none',
            '68'    => 'none',
            '70'    => 'none',
            '72'    => 'none',
            '75'    => 'none',
            '77'    => 'none',
            '79'    => 'none',
            '83'    => 'none',
            '84'    => 'none',
            '86'    => 'none',
            '88'    => 'none',
            '95'    => 'none',
            '100'   => 'none',
            '104'   => 'none',
            '105'   => 'none',
            '111'   => 'none',
            '117'   => 'none',
            '119'   => 'none',
            '120'   => 'none',
            '123'   => 'none',
            '124'   => 'none',
            '126'   => 'none',
            '129'   => 'none',
            '130'   => 'none',
            '133'   => 'none',
            '138'   => 'none',
            '139'   => 'none',
            '145'   => 'none',
            '146'   => 'none',
            '147'   => 'none',
            '150'   => 'none',
            '151'   => 'none',
            '155'   => 'none',
            '156'   => 'none',
            '157'   => 'none',
            '158'   => 'none',
            '159'   => 'none',
            '161'   => 'none',
            '166'   => 'none',
            '167'   => 'none',
            '182'   => 'none',
            '183'   => 'none',
            '190'   => 'none',
            '191'   => 'none',
            '192'   => 'none',
            '193'   => 'none',
            '195'   => 'none',
            '196'   => 'none',
            '199'   => 'none',
            '200'   => 'none',
            '201'   => 'none',
            '206'   => 'none',
            '207'   => 'none',
            '208'   => 'none',
            '218'   => 'none',
            '221'   => 'none',
            '222'   => 'none',
            '223'   => 'none',
            '228'   => 'none',
            '229'   => 'none',
            '644'   => 'none',
            '231'   => 'none',
            '238'   => 'none',
            '242'   => 'none',
            '244'   => 'none',
            '251'   => 'none',
            '259'   => 'none',
            '261'   => 'none',
            '262'   => 'none',
            '263'   => 'none',
            '271'   => 'none',
            '277'   => 'none',
            '350'   => 'none',
            '279'   => 'none',
            '286'   => 'none',
            '290'   => 'none',
            '295'   => 'none',
            '296'   => 'none',
            '301'   => 'none',
            '302'   => 'none',
            '304'   => 'none',
            '305'   => 'none',
            '306'   => 'none',
            '308'   => 'none',
            '311'   => 'none',
            '313'   => 'none',
            '314'   => 'none',
            '317'   => 'none',
            '318'   => 'none',
            '321'   => 'none',
            '322'   => 'none',
            '323'   => 'none',
            '324'   => 'none',
            '325'   => 'none',
            '326'   => 'none',
            '329'   => 'none',
            '332'   => 'none',
            '334'   => 'none',
            '337'   => 'none',
            '338'   => 'none',
            '339'   => 'none',
            '340'   => 'none',
            '341'   => 'none',
            '345'   => 'none',
            '346'   => 'none',
            '348'   => 'none',
            '349'   => 'none',
            '354'   => 'none',
            '356'   => 'none',
            '491'   => 'none',
            '357'   => 'none',
            '358'   => 'none',
            '363'   => 'none',
            '364'   => 'none',
            '369'   => 'none',
            '371'   => 'none',
            '377'   => 'none',
            '383'   => 'none',
            '388'   => 'none',
            '389'   => 'none',
            '392'   => 'none',
            '396'   => 'none',
            '399'   => 'none',
            '400'   => 'none',
            '401'   => 'none',
            '406'   => 'none',
            '407'   => 'none',
            '414'   => 'none',
            '415'   => 'none',
            '416'   => 'none',
            '418'   => 'none',
            '421'   => 'none',
            '425'   => 'none',
            '428'   => 'none',
            '431'   => 'none',
            '436'   => 'none',
            '442'   => 'none',
            '449'   => 'none',
            '451'   => 'none',
            '452'   => 'none',
            '456'   => 'none',
            '460'   => 'none',
            '461'   => 'none',
            '470'   => 'none',
            '472'   => 'none',
            '473'   => 'none',
            '474'   => 'none',
            '475'   => 'none',
            '480'   => 'none',
            '481'   => 'none',
            '482'   => 'none',
            '484'   => 'none',
            '487'   => 'none',
            '490'   => 'none',
            '493'   => 'A2-2628',   // colin-henri-1880-11-01
            '494'   => 'none',
            '501'   => 'none',
            '505'   => 'none',
            '510'   => 'none',
            '516'   => 'none',
            '518'   => 'none',
            '522'   => 'none',
            '525'   => 'none',
            '529'   => 'none',
            '530'   => 'none',
            '537'   => 'none',
            '538'   => 'none',
            '844'   => 'none',
            '541'   => 'none',
            '544'   => 'none',
            '545'   => 'none',
            '547'   => 'none',
            '553'   => 'none',
            '554'   => 'none',
            '558'   => 'none',
            '559'   => 'none',
            '561'   => 'none',
            '564'   => 'none',
            '565'   => 'none',
            '571'   => 'none',
            '573'   => 'none',
            '574'   => 'none',
            '575'   => 'none',
            '576'   => 'none',
            '578'   => 'none',
            '579'   => 'none',
            '580'   => 'none',
            '587'   => 'none',
            '590'   => 'none',
            '596'   => 'none',
            '599'   => 'none',
            '602'   => 'none',
            '604'   => 'none',
            '607'   => 'none',
            '612'   => 'none',
            '618'   => 'none',
            '620'   => 'none',
            '622'   => 'none',
            '624'   => 'none',
            '627'   => 'none',
            '633'   => 'none',
            '634'   => 'none',
            '638'   => 'none',
            '649'   => 'none',
            '655'   => 'none',
            '657'   => 'none',
            '663'   => 'none',
            '665'   => 'none',
            '666'   => 'none',
            '667'   => 'none',
            '669'   => 'none',
            '671'   => 'none',
            '673'   => 'none',
            '675'   => 'none',
            '676'   => 'none',
            '683'   => 'none',
            '686'   => 'none',
            '692'   => 'none',
            '693'   => 'none',
            '695'   => 'none',
            '696'   => 'none',
            '708'   => 'none',
            '709'   => 'none',
            '712'   => 'none',
            '716'   => 'none',
            '717'   => 'none',
            '718'   => 'none',
            '724'   => 'none',
            '729'   => 'none',
            '731'   => 'none',
            '736'   => 'none',
            '738'   => 'none',
            '743'   => 'none',
            '748'   => 'none',
            '752'   => 'none',
            '754'   => 'none',
            '756'   => 'none',
            '757'   => 'none',
            '759'   => 'none',
            '763'   => 'none',
            '767'   => 'none',
            '771'   => 'none',
            '774'   => 'none',
            '777'   => 'none',
            '786'   => 'none',
            '789'   => 'none',
            '790'   => 'none',
            '791'   => 'none',
            '794'   => 'none',
            '803'   => 'none',
            '805'   => 'none',
            '806'   => 'none',
            '809'   => 'none',
            '816'   => 'none',
            '826'   => 'none',
            '828'   => 'none',
            '840'   => 'none',
            '841'   => 'none',
            '842'   => 'none',
            '853'   => 'none',
            '854'   => 'none',
            '859'   => 'none',
            '866'   => 'none',
            '868'   => 'none',
            '872'   => 'none',
            '873'   => 'none',
            '876'   => 'none',
            '879'   => 'none',
        ],
    ];

} // end class

