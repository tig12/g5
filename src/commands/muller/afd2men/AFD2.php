<?php
/******************************************************************************
    Arno Müller's 612 famous men
    Code common to afd2
    
    @license    GPL
    @history    2021-09-05 04:36:32+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd2men;

use g5\Config;
use g5\model\DB5;
use g5\model\{Source, Group};
use tiglib\arrays\csvAssociative;

class AFD2 {
    
    /**
        Trust level for data
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        Path to the yaml file containing the characteristics of the source describing file
        data/raw/muller/afd2-men/muller-afd2-men.txt
        Relative to directory data/model/source
    **/
    const LIST_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd2-men-list.yml';

    /** Slug of source muller-afd2-men.txt **/
    const LIST_SOURCE_SLUG = 'afd2';
    
    /**
        Path to the yaml file containing the characteristics of Müller's booklet AFD2.
        Relative to directory data/model/source
    **/
    const BOOKLET_SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd2-men-booklet.yml';
    
    /** Slug of source Astro-Forschungs-Daten vol 2 **/
    const BOOKLET_SOURCE_SLUG = 'afd2-booklet';
    
    /** Slug of the group in db **/
    const GROUP_SLUG = 'muller-02-men';

    /**
        Limit of fields in the raw fields ; example for beginning of first line:
        1   Abbe, Ernst                         23.01.1840 21.30        LMT D   Eisenach
        |   |                                   |               |      |
        0   4                                   40              57     64
    **/
    const RAW_LIMITS = [
        0,
        4,
        40,
        51,
        57,
        64,
        68,
        71,
        96,
        103,
        113,
        118,
        124,
        127,
        128,
    ];
    
    /** Names of the columns of raw file **/
    const RAW_FIELDS = [
        'MUID',
        'NAME',
        'DATE',
        'TIME',
        'TZO',
        'TIMOD', // time mode
        'CY',
        'PLACE',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** Names of the columns of tmp csv file **/
    const TMP_FIELDS = [
        'MUID',
        'FNAME',
        'GNAME',
        'FAME',
        'NOBL',
        'DATE',
        'TZO',
        'TIMOD', // time mode
        'PLACE',
        'CY',
        'C1',
        'C2',
        'LAT',
        'LG',
        'OCCU',
        'BOOKS',
        'SOURCE',
        'GQ',
    ];
    
    /** 
        Match between Müller and Cura ids.
        Array built by look::look_gauquelin()
        Used by tmp2db::execute()
    **/
    const MU_GQ = [
        '10' => 'A6-864', // Annunzio Gabriele
        '102' => 'A5-1225', // Cage John
        '105' => 'D6-88', // Caracciola Rudolf
        '11' => 'A6-28', // Anouvilh Jean
        '110' => 'A4-202', // Cezanne Paul
        '113' => 'A2-3030', // Clair René
        '114' => 'D6-103', // Clark Jim
        '115' => 'A6-202', // Claudel Paul
        '118' => 'A2-162', // Clemenceau Georges
        '12' => 'A1-773', // Anguetil Jacques
        '124' => 'A4-250', // Corot Camille
        '126' => 'A4-253', // Courbet Gustave
        '128' => 'D6-113', // Cramm Gottfried
        '135' => 'A6-230', // Daudet Alphonse
        '136' => 'A4-269', // Daumier Honoré
        '137' => 'D10-304', // Davis Miles
        '138' => 'A4-1760', // Debussy Claude
        '143' => 'E3-469', // Delon Alain
        '153' => 'A3-1828', // Dornier Claudius
        '160' => 'A6-281', // Dumas Alexandre
        '169' => 'A2-3259', // Einstein Albert
        '171' => 'A6-293', // Eluard Paul
        '173' => 'A4-1385', // Ensor James
        '185' => 'A2-3027', // Fermi Enrico
        '186' => 'A5-320', //  
        '192' => 'A6-318', // Flaubert Gustave
        '198' => 'A6-334', // France Anatole
        '200' => 'E3-660', // Francois-Poncet André
        '211' => 'E3-669', // Gabin Jean
        '216' => 'A5-1646', // Gasperi Alcide
        '217' => 'A4-439', // Gauguin Paul
        '218' => 'A3-568', // Gaulle Charles
        '223' => 'D6-177', // Germar Manfred
        '225' => 'A6-364', // Gide André
        '229' => 'A5-1903', // Goebbels ageepe
        '231' => 'A3-1881', // Göring Hermann
        '234' => 'A4-1929', // Gounod Charles
        '24' => 'A5-1057', // Balser Ewald
        '248' => 'D6-199', // Hary Armin
        '272' => 'A4-1966', // Honegger Arthur
        '277' => 'A4-536', // Hugo Victor
        '286' => 'A6-436', // Jaurès Jean-Jacques
        '289' => 'A3-1956', // Jünger Ernst
        '292' => 'E3-100', // Junkers Hugo
        '3' => 'A5-1846', // Adenauer Konrad
        '322' => 'A5-1170', // Krauss Werner
        '328' => 'D6-242', // Lauer Martin
        '33' => 'A5-1059', // Bassermann Albert
        '330' => 'A4-637', // Léger Fernand
        '336' => 'A2-2787', // Lesseps Ferdinand
        '35' => 'A6-63', // Baudelaire Charles
        '353' => 'A6-1147', // Maeterlinck Maurice
        '355' => 'A6-1194', // Maillol Aristide
        '359' => 'A6-532', // Mallarmé Stéphane
        '360' => 'A4-706', // Manet Edouard
        '366' => 'A6-537', // Marcel Gabriel
        '375' => 'A6-558', // Maupassant Guy
        '376' => 'A6-559', // Mauriac Francois
        '388' => 'A4-2137', // Milhaud Darius
        '391' => 'A4-772', // Millet Jean-Francois
        '405' => 'A6-593', // Musset Alfred
        '406' => 'A5-1745', // Mussolini Benito
        '42' => 'A2-2569', // Becquerel Henri
        '428' => 'A2-555', // Pasteur Louis
        '430' => 'A3-2057', // Paulus Friedrich
        '436' => 'A3-866', // Petain Philippe
        '44' => 'E3-93', // Belmondo Jean-Paul
        '446' => 'A2-2853', // Poincaré Henri
        '447' => 'E3-1241', // Poincaré Raymond
        '452' => 'A6-657', // Proudhon Pierre
        '471' => 'A6-679', // Renan Ernest
        '473' => 'A4-946', // Renoir Auguste
        '48' => 'A4-1556', // Berlioz Hector
        '496' => 'A5-1238', // Rühmann Heinz
        '499' => 'A4-2260', // Saint-Saens Camille
        '52' => 'A2-1664', // Bier August
        '522' => 'D6-373', // Seeler Uwe
        '54' => 'A5-1064', // Birgel Willy
        '551' => 'A6-760', // Taine Hippolyte
        '552' => 'A5-727', // Tati Jacques
        '56' => 'A6-110', // Blondel Maurice
        '565' => 'A4-1078', // Toulouse-Lautrec Henri
        '57' => 'E3-140', // Blum Leon
        '571' => 'E3-1488', // Utrillo Maurice
        '573' => 'A4-1094', // Valery Paul
        '576' => 'A6-793', // Verlaine Paul
        '577' => 'A6-794', // Verne Jules
        '594' => 'D10-1339', // Welles Orson
        '599' => 'A2-2268', // Willistatter Richard
        '603' => 'D6-426', // Wolfshohl Rolf
        '66' => 'A1-2046', // Borotra Jean
        '70' => 'A4-1615', // Boulez Pierre
        '73' => 'D10-150', // Brando Marion
        '75' => 'A4-159', // Braque Georges
        '83' => 'A2-3583', // Brouwer Luitzen Egbertus
        '84' => 'D10-171', // Brubeck Dave
        '86' => 'A5-1864', // Brüning Heinrich
    ];
    
    /** 
        Associations Müller's Berufsgruppe / Tätigkeitsfeld => g5 occupation code
        Partly built by look::look_occus().
        Note: sometimes doesn't follow Müller, after checking on wikipedia.
        X means useless because handled by tweaks file.
    **/
    const OCCUS = [
/* 
        'AR 01' => 'fictional-writer', // 85 persons
        'AR 02' => 'factual-writer', // 12 persons
        'AR 03' => 'actor', // 43 persons
        'AR 04' => 'composer', // 1 persons
        'AR 06' => 'singer', // 21 persons
        'AR 07' => 'musician', // 3 persons
        'AR 08' => 'X', // 11 persons - more precise infos in tweaks file
        'SC 01' => 'mathematician', // 1 persons
        'SC 02' => 'X', // 1 persons - Irène Joliot-Curie - more precise infos in tweaks file
        'SC 03' => 'X', // 2 persons - more precise infos in tweaks file
        'SC 04' => 'physician', // 2 persons
        'SC 05' => 'social-scientist', // 8 persons
        'SC 06' => 'historian-of-science', // 1 persons
        'SC 07' => 'romanist', // 1 persons
        'WA 02' => 'aircraft-pilot', // 2 persons
        'WA 04' => 'politician', // 7 persons
        'WA 05' => 'religious-leader', // 2 persons
        'WA 06' => 'monarch', // 10 persons
        'WA 08' => 'revolutionary', // 2 persons
        'WA 09' => 'X', // 4 persons - more precise infos in tweaks file
        'WA 10' => 'suffragette', // 7 persons
        'WA 12' => 'partner-of-celebrity', // 8 persons
*/
    ];
    
    /** Conversion to ISO 3166. **/
    public const COUNTRIES = [
        'A'   => 'AT', // Austria
        'B'   => 'BE', // Belgium
        'CH'  => 'CH', // Switzerland
        'CS'  => 'CZ', // Czech Republic
        'D'   => 'DE', // Germany
        'DK'  => 'DK', // Denmark
        'DOP' => 'PL', // Former German regions, now Polish
        'DOS' => 'RU', // Former German regions, now Russia 
        'DZ'  => 'DZ', // Algeria
        'E'   => 'ES', // Spain
        'F'   => 'FR', // France
        'GB'  => 'GB', // Great Britain
        'I'   => 'IT', // Italy
        'L'   => 'LU', // Luxemburg
        'NL'  => 'NL', // Netherlands
        'S'   => 'SE', // Sweden
        'RCH' => 'CL', // Chile
        'USA' => 'US', // United States of America
    ];
    
    /** Admin code level 1. **/
    public const C1 = [
        /* 
        'Baselland'         => 'BL',
        'Basel-Stadt'       => 'BS',
        'Bern'              => 'BE',
        'Ca.'               => 'CA',
        'Emmental, Bern'    => 'BE',
        'Graubünden'        => 'GR',
        'Ill.'              => 'IL',
        'Luzern'            => 'LU',
        'N.H.'              => 'NH',
        'N.J.'              => 'NJ',
        'Minn.'             => 'MN',
        'Nevenburg'         => 'NE',
        'Ohio'              => 'OH',
        'Pa.'               => 'PA',
        'St. Gallen'        => 'SG',
        'Waadt'             => 'VD',
        'Wash.'             => 'WA',
        */
    ];
    
    /**
        Admin code level 2.
    **/
    public const C2 = [
/* 
        'Ancona'            => 'AN',
        'Ancona, Rom'       => 'AN',
        'Antwerpen'         => 'VAN',
        'Bologna'           => 'BO',
        'Briissel'          => 'BRU',
        'Calvados'          => '14',
        'Cher'              => '18',
        'Deux-Sévres'       => '79',
        //'Donan'             => '',
        'Dordogne'          => '19',
        'Dresden'           => '',
        //'Elster, Merseburg' => '',
        //'Erzgebirge'        => '',
        //'Fehrbellin, Brandenbg.' => '',
        //'Harz'              => '',
        //'Icking, Oberb.'    => '',
        //'Innsbruck, Tirol'  => '',
        //'Karnten'           => '',
        //'Lavanttal'         => '',
        //'Liitzen'           => '',
        'Lot'               => '46',
        //'Meifen, Sachsen'   => '',
        //'Oder'              => '',
        'Oise'              => '60',
        //'Ostpriegnitz'      => '',
        'Paris'             => '75',
        'Pavia, Lombardei'  => 'PV',
        //'Pegau, Sachsen'    => '',
        //'Rigen'             => '',
        //'Rochlitz, Sachsen' => '',
        'Rom'               => 'RM',
        //'Sachsen'           => '',
        'Sardinien'         => 'NU', // Nuoro
        'Seine, Paris'      => '75',
        //'Steiermark'        => '',
        //'Thüringen'         => '',
        //'Tirol'             => '',
        'Turin'             => 'TO',
        'Vendée'            => '85',
        'Yonne'             => '89',
*/
    ];
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for raw file. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Group management ***********************
    
    /**
        Returns a Group object for Muller612.
    **/
    public static function getGroup(): Group {
        $g = new Group();
        $g->data['slug'] = self::GROUP_SLUG;
        $g->data['name'] = "Müller 612 famous men";
        $g->data['type'] = Group::TYPE_HISTORICAL;
        $g->data['description'] = "612 famous men, gathered by Arno Müller";
        $g->data['sources'][] = self::LIST_SOURCE_SLUG;
        return $g;
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file, built from scans.
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'muller', 'afd2-men', 'muller-afd2-men.txt']);
    }
    
    /** Loads 5muller_writers.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
                                                                                         
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd2-men', 'muller-afd2-men.csv']);
    }
    
    /**
        Loads the tmp file in a regular array
        @return Regular array ; each element is an assoc array containing the fields
    **/
    public static function loadTmpFile(){
        return csvAssociative::compute(self::tmpFilename());
    }                                                                                              
    
    /**
        Loads the file temporary file in an asssociative array ; keys = MUID
    **/
    public static function loadTmpFile_muid(){
        $rows1 = self::loadTmpFile();
        $res = [];              
        foreach($rows1 as $row){
            $res[$row['MUID']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of the "tmp raw file", eg. data/tmp/newalch/1083MED-raw.csv
        (file used to keep trace of the original raw values).
    **/
    public static function tmpRawFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'muller', 'afd2-men', 'muller-afd2-men-raw.csv']);
    }
    
    /** Loads the "tmp raw file" in a regular array **/
    public static function loadTmpRawFile(){
        return csvAssociative::compute(self::tmpRawFilename());
    }
    
} // end class
