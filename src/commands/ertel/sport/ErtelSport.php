<?php
/******************************************************************************
    Code common to sport
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-11 23:15:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\ertel\sport;

use g5\app\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;
use g5\commands\ertel\Ertel;

class ErtelSport {
    
    /**
        Trust level for data coming from Ertel file
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    
    // ******************************************************
    /**
        Computes field GQID, string like "A1-123" from one line of file 3a_sports 
        @param  $rawLine    Associative array, keys = field names: NR, G_NR ...
    **/
    public static function GQIDfrom3a_sports(&$rawLine){
        if(substr($rawLine['QUEL'], 0, 2) != 'G:'){
            return '';
        }
        $GQID = '';
        $rest = substr($rawLine['QUEL'], 2);
        switch($rest){
        	case 'A01': $GQID = 'A1'; break;
        	case 'D06': $GQID = 'D6'; break;
        	case 'D10': $GQID = 'D10'; break;
        }
        $GQID .= '-' . $rawLine['G_NR'];
        return $GQID;
    }
    
    // *********************** Source management ***********************
    
    /**
        Path to the yaml file containing the characteristics of the source describing file 3a_sports.txt.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'ertel' . DS . '3a_sports.yml';
    
    /** Slug of source 3a_sports.txt **/
    const SOURCE_SLUG = '3a_sports';
    
    // *********************** Group management ***********************
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'ertel-4384-sport';
    
    /**
        Path to the yaml file containing the characteristics of the group ertel-4384-sportsmen.
        Relative to directory data/db/group
    **/
    const GROUP_DEFINITION_FILE = 'ertel' . DS. self::GROUP_SLUG . '.yml';
    
    /** Slugs of ertel-4384-sportsmen subgroups **/
    const SUBGROUP_SLUGS = [
        'ertel-1-first-french',
        'ertel-2-first-european',
        'ertel-3-italian-football',
        'ertel-4-german-various',
        'ertel-5-french-occasionals',
        'ertel-6-para-champions',
        'ertel-7-para-lowers',
        'ertel-8-csicop-us',
        'ertel-9-second-european',
        'ertel-10-italian-cyclists',
        'ertel-11-lower-french',
        'ertel-12-gauq-us',
        'ertel-13-plus-special',
    ];
    
    /** Returns an empty Group object for ertel-4384-sportsmen. **/
    public static function getGroup(): Group {
        return Group::createFromDefinitionFile(self::GROUP_DEFINITION_FILE);
    }
    
    /** Returns a Group object for a given subgroup. **/
    public static function getSubgroup(string $slug): Group {
        return Group::createFromDefinitionFile('ertel' . DS . $slug . '.yml');
    }
    
    // *********************** Raw file manipulation ***********************
    
    /**
        @return Path to the raw file coming from newalchemypress.com
    **/
    public static function rawFilename(){
        return Ertel::rawDirname() . DS . '3a_sports-utf8.txt';
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /** Path to the temporary csv file used to work on this group. **/
    public static function tmpFilename(){
        return Ertel::tmpDirname() . DS . self::GROUP_SLUG . '.csv';
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
    
    /** Path to the temporary csv file keeping an exact copy of the raw file. **/
    public static function tmpRawFilename(){
        return Ertel::tmpDirname() . DS . self::GROUP_SLUG . '-raw.csv';
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
    // *********************** Tweak file manipulation ***********************
    
    public static function tweakFilename(){
        return Config::$data['dirs']['init'] . DS . 'newalch-tweak' . DS . self::GROUP_SLUG . '.yml';
    }
    
    // *********************** Country management ***********************
    
    /** Mapping between country code used in raw file (field NATION) and ISO 3166 country code. **/
    const RAW_NATION_CY = [
        'USA' => 'US',
        'FRA' => 'FR',
        'ITA' => 'IT',
        'BEL' => 'BE',
        'GER' => 'DE',
        'SCO' => 'GB',
        'NET' => 'NL',
        'LUX' => 'LU',
        'SPA' => 'ES',
    ];
    
    // *********************** Occupation management ***********************
    
    /** Mapping between sport code used in raw file (field SPORT) and g5 occupation slug. **/
    const RAW_SPORT_OCCU = [
        'AIRP'      => 'aircraft-pilot',
        'BADM'      => 'badminton-player',
        'BASE'      => 'baseball-player',
        'BASK'      => 'basketball-player',
        'BILL'      => 'billard-player',
        'BOBSL'     => 'bobsledder',
        'BOWL'      => 'bowler',
        'BOXI'      => 'boxer',
        'CANO'      => 'canoeist',
        'ALPI'      => 'mountaineer',
        'CYCL'      => 'cyclist',
        'HORS'      => 'equestrian',
        'FENC'      => 'fencer',
        'HOCK'      => 'field-hockey-player',
        // see function computeSport()
        //'FOOT'      => 'american-football-player',
        //'FOOT'      => 'football-player',
        'GOLF'      => 'golfer',
        'GYMN'      => 'gymnast',
        'HAND'      => 'handball-player',
        'JUDO'      => 'judoka',
        'AUTO'      => 'motor-sports-competitor',
        'MOTO'      => 'motor-sports-competitor',
        'PELOT'     => 'basque-pelota-player',
        'WALK'      => 'race-walker',
        'RODE'      => 'rodeo-rider',
        'ROLL'      => 'roller-skater',
        'AVIR'      => 'rower',
        'ROWI'      => 'rower',
        'RUGB'      => 'rugby-player',
        'YACH'      => 'sport-sailer',
        'SHOO'      => 'sport-shooter',
        'SKII'      => 'skier',
        'SWIM'      => 'swimmer',
        'TENN'      => 'tennis-player',
        'TRAC'      => 'athletics-competitor',
        'VOLL'      => 'volleyball-player',
        'WEIG'      => 'weightlifter',
        'ICES'      => 'winter-sports-practitioner',
        'WRES'      => 'wrestler',
    ];
    
    // ******************************************************
    /**
        Converts a sport code used in Ertel file to an occupation code used in g5.
        @param  $line Associative array containing a line of the tmp file.
    **/
    public static function computeSport($line) {
        $sport = $line['SPORT'];
        if($sport != 'FOOT'){
            return self::RAW_SPORT_OCCU[$sport];
        }
        // FOOT
        if($line['CY'] == 'US'){
            return 'american-football-player';
        }
        return 'football-player';
    }
    
    /**
        Associations Ertel id (column NR) => CFEPP id (column CFEPNR).
        Permits to fix the wrong associations contained in Ertel file.
        Array built from succesive executions of command
        php run-g5.php cfepp final3 look ertel
    **/
    const ERTEL_CFEPP = [
        2 => 790, // Alain Abadie
        4 => 791, // Lucien Abadie
        42 => 792, // Pierre Albaladejo
        70 => 793, // André Alvarez
        74 => 794, // Henri Amand
        80 => 795, // Jean-Baptiste Amestoy
        123 => 796, // Roger Arcalis
        138 => 798, // Michel Arnaudet
        153 => 799, // Richard Astre
        172 => 800, // Louis Azarette
        196 => 801, // Marcel Bailette
        271 => 802, // Guy Basquet
        274 => 803, // Jean Pierre Bastiat
        285 => 804, // Noel Baudry
        288 => 805, // Robert Baulon
        293 => 806, // Jacques Bayardon
        314 => 807, // Louis Beguet
        316 => 808, // André Behoteguy
        317 => 809, // Henri Behoteguy
        343 => 810, // René Benesis
        354 => 811, // André Beraud
        356 => 812, // Jean Claude Berejnoi
        382 => 813, // Jean Louis Berot
        437 => 814, // Paul Biemouret
        444 => 816, // Eugene Billac
        512 => 1021, // Henri Bollelli
        532 => 817, // André Boniface
        533 => 818, // Guy Boniface
        559 => 820, // Dominique Bontemps
        567 => 821, // Francois Borde
        572 => 822, // Léon Bornenave
        599 => 823, //  Christian Boujet
        604 => 824, // Jacques Bouquet
        610 => 825, // Roger Bourdeu
        611 => 826, // Roger Bourgarel
        663 => 827, // René Brejassou
        693 => 828, // Georges Brun
        724 => 829, // Yvan Buonomo
        761 => 831, // Eugene Buzy
        763 => 832, // Jean Michel Cabanier
        791 => 833, // Guy Camberabero
        792 => 834, // Lilian Camberabero
        798 => 835, // Fernand Camicas
        800 => 836, // André Campaes
        813 => 837, // Jack Cantoni
        853 => 838, // Lucien Caron
        864 => 839, // Christian Carrere
        865 => 840, // Jean Carrere
        909 => 841, // Jean Caujolle
        926 => 842, // Albert Cazenave
        927 => 843, // Fernand Cazenave
        933 => 844, // Michel Celaya
        1074 => 846, // Marcel Communeau
        1122 => 38, // Jean Pierre Corval
        1151 => 847, // René Crabos
        1154 => 848, // Jacques Crampagne
        1155 => 849, // Roland Crancee
        1156 => 850, // Michel Crauste
        1222 => 851, // Christian Darrouy
        1223 => 852, // Benoit Dauga
        1245 => 854, // Nicolas De Gregorio
        1294 => 855, // Louis Dedet
        1313 => 856, // Jean Louis Dehez
        1370 => 857, // Francis Desclaux
        1422 => 858, // Pierre Dizabo
        1430 => 859, // Henri Domec
        1431 => 860, // Amédée Domenech
        1453 => 861, // Claude Dourthe
        1478 => 862, // Gérard Dufau
        1479 => 863, // Jacques Dufourcq
        1496 => 864, // Clément Dupont
        1498 => 865, // Louis Dupont 
        1499 => 866, // Bernard Duprat
        1504 => 867, // Jean Dupuy
        1514 => 868, // Bernard Dutin
        1553 => 870, // Alain Esteve
        1558 => 871, // Marc Etcheverry
        1647 => 873, // Roger Fite
        1698 => 874, // Jacques Fouroux
        1706 => 875, // André Franquenelle
        1744 => 876, // Jean Gachassin 
        1821 => 877, // Roger Gensane
        1827 => 878, // Geo Gerald
        1838 => 879, // Francois Gesta-Lavit
        1954 => 853, // Jean de Gregorio
        1899 => 880, // Charles Gonnet
        1907 => 881, // Raoul Got
        1941 => 882, // Vincent Graule
        1950 => 883, // Michel Greffe
        2046 => 884, // Raymond Halcaren
        2076 => 885, // Michel Hauser
        2116 => 886, // André Herrero
        2117 => 887, // Daniel Herrero
        2176 => 888, // Henri Iharassary
        2183 => 889, // Jean Iracabal
        2207 => 890, // Adolphe Jauréguy
        2223 => 891, // Marcel Jol
        2239 => 892, // Louis Junquas
        2301 => 893, // Paul Labadie
        2304 => 894, // Antoine Labazuy
        2305 => 895, // Claude Laborde
        2309 => 896, // Claude Lacaze
        2320 => 897, // Pierre Lacroix
        2373 => 899, // Jean Lassegue
        2383 => 901, // Marcel Laurent
        2425 => 765, // Jean-Pierre Lecompte
        2469 => 766, // Odé Lespes
        2644 => 767, // Claude Mantoulan
        2693 => 768, // Serge Marsolan
        2722 => 769, // Francis Mas
        2770 => 771, // Hervé Mazard
        2772 => 772, // Louis Mazon
        2905 => 773, // Michel Mollinier
        3410 => 775, // Aldo Quaglio
        3456 => 776, // Raymond Rebujent
        3485 => 777, // Roger Rey
        3602 => 778, // Max Rousié
        // Possible confusion between Francis and Antoine Rudler
        // Seems that 2 distinct basket-ball players have existed :
        // https://www.wikiwand.com/en/Foyer_alsacien_Mulhouse
        // 3619 => 218, // Antoine Rudler
        3637 => 779, // Christian Sabatie
        3696 => 780, // Jean Pierre Sauret
        3704 => 781, // André Savonne
        3835 => 782, // Michel Sitjar
        3960 => 783, // Pierre Taillantou
        4053 => 784, // Raymond Toujas
        4061 => 785, // Frédéric Trescases
        4088 => 786, // Amboise Ulma
        4100 => 787, // André Vadon
        4200 => 788, // Louis Verge
        4282 => 789, // Maurice Voron
    ];
    
    
} // end class
