<?php
/******************************************************************************
    Arno Müller's 612 famous men
    Code common to afd2
    
    @license    GPL
    @history    2021-09-05 04:36:32+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd2men;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Source;
use g5\model\Group;
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
        '3' => 'A5-1846', // Adenauer Konrad 
        '10' => 'A6-864', // Annunzio Gabriele 
        '11' => 'A6-28', // Anouilh Jean 
        '12' => 'A1-773', // Anguetil Jacques 
        '15' => 'A6-29', // Apollinaire Guillaume 
        '19' => 'A6-50', // Ayme Marcel 
        '24' => 'A5-1057', // Balser Ewald 
        '28' => 'A5-67', // Barrault Jean-Louis 
        '33' => 'A5-1059', // Bassermann Albert 
        '34' => 'A2-1646', // Bastian Adolf 
        '35' => 'A6-63', // Baudelaire Charles 
        '41' => 'A3-1758', // Beck Ludwig 
        '42' => 'A2-2569', // Becquerel Henri 
        '44' => 'E3-93', // Belmondo Jean-Paul 
        '48' => 'A4-1556', // Berlioz Hector 
        '49' => 'A6-89', // Bernanos Georges 
        '52' => 'A2-1664', // Bier August 
        '54' => 'A5-1064', // Birgel Willy 
        '56' => 'A6-110', // Blondel Maurice 
        '57' => 'E3-140', // Blum Leon 
        '63' => 'A4-139', // Bonnard Pierre 
        '66' => 'A1-2046', // Borotra Jean 
        '70' => 'A4-1615', // Boulez Pierre 
        '73' => 'D10-150', // Brando Marion 
        '74' => 'A5-1861', // Brandt Willy 
        '75' => 'A4-159', // Braque Georges 
        '80' => 'A6-148', // Breton André 
        '81' => 'E3-212', // Briand Aristide 
        '82' => 'A2-2604', // Broglie Louis-Victor Duc 
        '83' => 'A2-3583', // Brouwer Luitzen Egbertus 
        '84' => 'D10-171', // Brubeck Dave 
        '86' => 'A5-1864', // Brüning Heinrich 
        '100' => 'D10-185', // Button Richard 
        '102' => 'D10-190', // Cage John 
        '103' => 'A6-168', // Camus Albert 
        '104' => 'A3-1800', // Canaris Wilhelm 
        '105' => 'D6-88', // Caracciola Rudolf 
        '106' => 'A2-3240', // Carnap Rudolf 
        '110' => 'A4-202', // Cezanne Paul 
        '114' => 'D6-103', // Clark Jim 
        '115' => 'A6-202', // Claudel Paul 
        '118' => 'A2-162', // Clemenceau Georges 
        '121' => 'A4-237', // Cocteau Jean 
        '123' => 'A1-924', // Coppi Fausto 
        '124' => 'A4-250', // Corot Camille 
        '126' => 'A4-253', // Courbet Gustave 
        '128' => 'D6-113', // Cramm Gottfried 
        '129' => 'A5-1636', // Croce Benedetto 
        '135' => 'A6-230', // Daudet Alphonse 
        '136' => 'A4-269', // Daumier Honoré 
        '137' => 'D10-304', // Davis Miles 
        '138' => 'A4-1760', // Debussy Claude 
        '140' => 'A4-281', // Degas Edgar 
        '143' => 'E3-469', // Delon Alain 
        '145' => 'A4-300', // Derain André 
        '153' => 'A3-1828', // Dornier Claudius 
        '157' => 'A6-276', // Duchamp Marcel 
        '158' => 'A2-239', // Duhamel Georges 
        '160' => 'A6-281', // Dumas Alexandre 
        '169' => 'A2-3259', // Einstein Albert 
        '171' => 'A6-293', // Eluard Paul 
        '173' => 'A4-1385', // Ensor James 
        '174' => 'A5-1885', // Erhard Ludwig 
        '182' => 'A4-1860', // Fauré Gabriel 
        '185' => 'A2-3027', // Fermi Enrico 
        '186' => 'A5-320', //   Fernandel
        '192' => 'A6-318', // Flaubert Gustave 
        '194' => 'A2-2689', // Foch Ferdinand 
        '198' => 'A6-334', // France Anatole 
        '200' => 'E3-660', // Francois-Poncet André 
        '211' => 'E3-669', // Gabin Jean 
        '213' => 'E3-682', // Gambetta Leon 
        '216' => 'A5-1646', // Gasperi Alcide 
        '217' => 'A4-439', // Gauguin Paul 
        '218' => 'A3-568', // Gaulle Charles 
        '223' => 'D6-177', // Germar Manfred 
        '225' => 'A6-364', // Gide André 
        '227' => 'A6-372', // Giraudoux Jean 
        '229' => 'A5-1903', // Goebbels ageepe 
        '231' => 'A3-1881', // Göring Hermann 
        '233' => 'A4-1444', // Gogh Vincent van 
        '234' => 'A4-1929', // Gounod Charles 
        '239' => 'A5-1112', // Goebel Heinrich 
        '239' => 'A5-1112', // Gründgens Gustaf 
        '244' => 'A2-3304', // Hahn Otto 
        '248' => 'D6-199', // Hary Armin 
        '257' => 'D10-582', // Herman Woody 
        '264' => 'A5-1931', // Himmler Heinrich 
        '272' => 'A4-1966', // Honegger Arthur 
        '277' => 'A4-536', // Hugo Victor 
        '286' => 'A6-436', // Jaurès Jean-Jacques 
        '289' => 'A3-1956', // Jünger Ernst 
        '290' => 'A5-1149', // Jürgens Curd 
        '307' => 'A4-1324', // Kirchner Ernst Ludwig 
        '322' => 'A5-1170', // Krauss Werner 
        '327' => 'A2-3328', // Laue Max 
        '328' => 'D6-242', // Lauer Martin 
        '330' => 'A4-637', // Léger Fernand 
        '336' => 'A2-2787', // Lesseps Ferdinand 
        '347' => 'A2-3606', // Lorentz Hendrik Antoon 
        '350' => 'A5-1977', // Lübke Heinrich 
        '353' => 'A6-1147', // Maeterlinck Maurice 
        '355' => 'A6-1194', // Maillol Aristide 
        '358' => 'A6-909', // Malaparte Curzio 
        '359' => 'A6-532', // Mallarmé Stéphane 
        '360' => 'A4-706', // Manet Edouard 
        '365' => 'A5-521', // Marais Jean 
        '366' => 'A6-537', // Marcel Gabriel 
        '367' => 'A2-3071', // Marconi Guglielmo 
        '370' => 'A6-548', // Martin du Gard Roger 
        '374' => 'A4-742', // Matisse Henri 
        '375' => 'A6-558', // Maupassant Guy 
        '376' => 'A6-559', // Mauriac Francois 
        '385' => 'A4-2130', // Messiaen Oliver 
        '388' => 'A4-2137', // Milhaud Darius 
        '391' => 'A4-772', // Millet Jean-Francois 
        '392' => 'A5-1204', // Minetti Bernhard 
        '394' => 'A4-1212', // Modigliani Amedeo 
        '396' => 'A4-1456', // Mondrian Piet 
        '397' => 'A6-585', // Montherlant Henry 
        '399' => 'A5-1742', // Moro Aldo 
        '405' => 'A6-593', // Musset Alfred 
        '406' => 'A5-1745', // Mussolini Benito 
        '410' => 'A5-1751', // Nenni Pietro 
        '426' => 'A5-2006', // Papen Franz 
        '428' => 'A2-555', // Pasteur Louis 
        '430' => 'A3-2057', // Paulus Friedrich 
        '433' => 'D10-1006', // Peck Gregory 
        '436' => 'A3-866', // Petain Philippe 
        '438' => 'A5-627', // Philipe Gerard 
        '441' => 'A2-3343', // Piccard Auguste 
        '446' => 'A2-2853', // Poincaré Henri 
        '447' => 'E3-1241', // Poincaré Raymond 
        '449' => 'A5-1221', // Ponto Erich 
        '450' => 'A2-3346', // Prandtl Ludwig 
        '452' => 'A6-657', // Proudhon Pierre 
        '453' => 'A6-658', // Proust Marcel 
        '456' => 'A5-1225', // Quadflieg Will 
        '458' => 'A6-665', // Queneau Raymond 
        '467' => 'A4-2218', // Ravel Maurice 
        '471' => 'A6-679', // Renan Ernest 
        '473' => 'A4-946', // Renoir Auguste 
        '479' => 'A6-689', // Rimbaud Arthur 
        '487' => 'A6-697', // Rolland Romain 
        '488' => 'A6-700', // Romains Jules 
        '489' => 'A3-2098', // Rommel Erwin 
        '494' => 'A4-979', // Rousseau Henri 
        '496' => 'A5-1238', // Rühmann Heinz 
        '498' => 'A1-223', // Saint-Exupéry Antoine 
        '499' => 'A4-2260', // Saint-Saens Camille 
        '500' => 'A6-727', // Sartre Jean-Paul 
        '501' => 'A2-2110', // Sauerbruch Ferdinand 
        '518' => 'E3-1384', // Schuman Robert 
        '522' => 'D6-373', // Seeler Uwe 
        '524' => 'A4-1030', // Seurat Georges 
        '526' => 'A5-868', // Sica Vittorio 
        '550' => 'A6-758', //   Sully Prudhomme
        '551' => 'A6-760', // Taine Hippolyte 
        '552' => 'A5-727', // Tati Jacques 
        '554' => 'A2-2898', // Teilhard de Chardin Pierre 
        '559' => 'A4-1359', // Thoma Hans 
        '563' => 'A5-1829', // Togliatti Palmiro 
        '565' => 'A4-1078', // Toulouse-Lautrec Henri 
        '571' => 'E3-1488', // Utrillo Maurice 
        '573' => 'A4-1094', // Valery Paul 
        '576' => 'A6-793', // Verlaine Paul 
        '577' => 'A6-794', // Verne Jules 
        '585' => 'D6-420', // Walter Fritz 
        '594' => 'D10-1339', // Welles Orson 
        '597' => 'A2-3392', // Wieland Heinrich 
        '599' => 'A2-2268', // Willistatter Richard 
        '600' => 'D6-424', // Winkler Hans Günter 
        '603' => 'D6-426', // Wolfshohl Rolf 
        '607' => 'A2-3396', // Ziegler Karl 
        '611' => 'A6-813', // Zola Emile 
    ];

    /** 
        Associations Müller's Berufsgruppe / Tätigkeitsfeld => g5 occupation code
        Partly built by look::look_occus().
        Note: sometimes doesn't follow Müller, after checking on wikipedia.
        X means useless because handled by tweaks file.
    **/
        
    const OCCUS = [
        'AR 01' => 'fictional-writer', // 113 persons
        'AR 02' => 'factual-writer', // 5 persons
        'AR 03' => 'actor', // 58 persons
        'AR 04' => 'composer', // 46 persons
        'AR 05' => 'conductor', // 11 persons
        'AR 06' => 'singer', // 8 persons
        'AR 07' => 'musician', // 12 persons
        'AR 08' => 'artist', // 70 persons - painter or sculptor => loss of information
        'AR 09' => 'architect', // 8 persons
        
        'MA 01' => 'miscelaneous', // 29 persons - Engineer, Inventor - meaningless, should be treated one by one
        'MA 02' => 'miscelaneous', // 3 persons
        'MA 03' => 'executive', // 9 persons
        'MA 04' => 'politician', // 42 persons
        'MA 05' => 'religious-leader', // 7 persons
        'MA 06' => 'monarch', // 10 persons
        'MA 07' => 'military-personnel', // 12 persons
        'MA 08' => 'revolutionary', // 6 persons
        'MA 09' => 'miscelaneous', // 5 persons - Reformer - meaningless, should be treated one by one
        'MA 13' => 'sportsperson', // 32 persons
        
        'SC 01' => 'mathematician', // 6 persons
        'SC 02' => 'scientist', // 24 persons - Physicist, Astronomer => loss of information
        'SC 03' => 'scientist', // 21 persons - Natural scientist => loss of information
        'SC 04' => 'physician', // 4 persons
        'SC 05' => 'social-scientist', // 13 persons
        'SC 06' => 'humanities-scholar', // 29 persons - Philosopher, Theologian => loss of information
        'SC 07' => 'humanities-scholar', // 19 persons
        'SC 08' => 'jurist', // 1 persons
        'SC 09' => 'political-economist', // 9 persons
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
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
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
