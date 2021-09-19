<?php
/********************************************************************************
    Common to cura A files
                                   
    @license    GPL
    @history    2019-12-26 22:32:10+01:00, Thierry Graff : creation from raw2csv
********************************************************************************/
namespace g5\commands\cura\A;

use g5\commands\cura\Cura;

class A{
    
    /** Names of the columns of raw A files (cura html pages) **/
    const RAW_FIELDS = [
        'YEA',
        'MON',
        'DAY',
        'PRO',
        'NUM',
        'COU',
        'H',
        'MN',
        'SEC',
        'TZ',
        'LAT',
        'LON',
        'COD',
        'CITY',
    ];
    
    /** Names of the columns of A files in data/tmp/cura **/
    const TMP_FIELDS = [
        'NUM',
        'FNAME',
        'GNAME',
        'OCCU',
        'DATE-UT',
        'PLACE',
        'CY',
        'C2',
        'C3',
        'LG',
        'LAT',
        'GEOID',
        'NOTES',
    ];
    
    /** Names of the columns of generated csv files used by default by export. **/
    const OUTPUT_FIELDS = [
        'NUM',
        'FNAME',
        'GNAME',
        'OCCU',
        'DATE-UT',
        'DATE',
        'TZO',
        'PLACE',
        'CY',
        'C2',
        'C3',
        'LG',
        'LAT',
        'GEOID',
    ];
    
    /**
        Associations between profession codes used in the cura html files
        and profession codes used in the generated csv files
        for the different files of serie A.
        These associations are used when no further details are provided
    **/
    const PROFESSIONS_NO_DETAILS = [
        'A1' => ['C' => 'sportsperson'],
        'A2' => ['S' => 'scientist'],
        'A3' => ['M' => 'military-personnel'],
        'A4' => ['P' => 'painter', 'M' => 'musician'],
        'A5' => ['A' => 'actor', 'PT' => 'politician'],
        'A6' => ['W' => 'writer', 'J' => 'journalist'], 
    ];
    
    /** 
        More detailed professions
        ex : in file 902gdA1y, profession of persons numbered between 1 and 86 (inclusive) is Athlétisme
        These informations come from the notices of cura.free.fr.
    **/
    const PROFESSIONS_DETAILS = [
        'A1' => [
            ['athletics-competitor',        1, 86],
            ['motor-sports-competitor',     87, 122],
            ['aircraft-pilot',              123, 514],
            ['rower',                       515, 522],
            ['basketball-player',           523, 555],
            ['billard-player',              556, 564],
            ['boxer',                       565, 768],
            ['canoeist+kayaker',            769, 769],
            ['cyclist',                     770, 1226],
            ['fencer',                      1227, 1242],
            ['football-player',             1243, 1690],
            ['golfer',                      1691, 1698],
            ['gymnast',                     1699, 1710],
            ['weightlifter',                1711, 1726],
            ['handball-player',             1727, 1730],
            ['field-hockey-player',         1731, 1741],
            ['wrestler',                    1742, 1751],
            ['race-walker',                 1752, 1757],
            ['swimmer',                     1758, 1784],
            ['basque-pelota-player',        1785, 1802],
            ['rugby-player',                1803, 2009],
            ['skier',                       2010, 2026],
            ['equestrian',                  2027, 2037],
            ['winter-sports-practitioner',  2038, 2040],
            ['tennis-player',               2041, 2075],
            ['sport-shooter',               2076, 2085],
            ['sport-sailer',                2086, 2088],
            ['volleyball-player',           2089, 2089],
        ],
        'A2' => [
            ['physician',                   1, 2552],
            ['scientist',                   2553, 3647], 
        ],
        // nothing for A3
        'A4' => [
            ['artist',                      1, 1473],
            ['musician',                    1474, 2339],
            ['conductor-of-military-band',  2340, 2722] 
        ],
        'A5' => [
            ['actor',                       1, 1409],
            ['politician',                  1410, 2412],
        ],
        // nothing for A6
    ];

    /** Mapping from country codes to iso3166 codes ; applies to all files of serie A **/
    const COUNTRIES = [
        'F' => 'FR',
        'I' => 'IT',
        'G' => 'DE',
        'B' => 'BE',
        'N' => 'NL',
        'S' => 'CH',
    ];
    
    /** 
        Manual corrections : name matching added using lists published by Gauquelin in 1955.
        Asoociative array : [
                serie => [
                    NUM => name,
                    ...
                ],
                ...
            ]
        Name spelling is the exact spelling contained in gd902N.html.
        This exact spelling is used to solve ambiguities.
        It may differ from 1955 book spelling.
    **/
    const CORRECTIONS_1955 = [
        // coming from 570 sportifs
        'A1' => [
            '10' => 'Bernard Henri',
            '55' => 'Lunis Jacques',
            '78' => 'Vernier Jacques',
            '79' => 'Vernier Jean',
            '88' => 'Chiron Louis',
            '520' => 'Nosbaum Guy',
            '539' => 'Faucherre Jacques',
            '540' => 'Flouret Jacques',
            '544' => 'Guillou Fernand',
            '561' => 'Grange Felix',
            '605' => 'Famechon Andre',
            '618' => 'Gyde Praxille',
            '795' => 'Chocque Paul',
            '798' => 'Cloarec Pierre',
            '817' => 'Galateau Fabien',
            '819' => 'Gauthier Bernard',
            '833' => 'Jacoponelli Pieere',
            '842' => 'Le Calvez Leon',
            '859' => 'Mithouard Fernand',
            '840' => 'Raynaud Andre',
            '871' => 'Remy Raoul',
            '893' => 'Vietto Rene',
            '1251' => 'Baratte Jean',
            '1261' => 'Bigot Jules',
            '1290' => 'Defosse Robert',
            '1322' => 'Heisserer Oscar',                                                                                                                        
            '1326' => 'Jacques Michel',
            '1368' => 'Rigal Jean',
            '1380' => 'Sesia Georges',
            '1748' => 'Jourlin Jean',
            '1770' => 'Laurent Robert',
            '1784' => 'Robert Raoul',
            '1796' => 'Hourcade Francois',
            '1797' => 'Lemoine Jean',
            '1827' => 'Berthomieu Gabriel',
            '1846' => 'Brunetaud Maurice',
            '1850' => 'Calixte Gaston',
            '1931' => 'Lassegue Jean',
            '1976' => 'Puig-Aubert Henri',
            '1997' => 'Taillantou Pierre',
            '1999' => 'Terreau Maurice',
            '2022' => 'Pazzi Jean',
            '1769' => 'Jeanne Yvonne',
            '2050' => 'Cochet Henri',
            '2060' => 'Jalabert Paul',
            '2076' => 'Bonin Marcel',
            '2077' => 'Coquelin Lisle Pierre',
            '2078' => 'Durand Raymond',
            '574' => 'Benedetto Valere',
            '575' => 'Bini Dante',
            '577' => 'Bouquet Jules',
            '585' => 'Ceustermans Serge',
            '589' => 'Clavel Michel',
            '592' => 'Colin Charles',
            '593' => 'Couet Andre',
            '608' => 'Gade Roger',
            '613' => 'Granger Francois',
            '625' => 'Humez Charles',
            '633' => 'Lapourielle Claude',
            '634' => 'Lapourielle Michel',
            '635' => 'Laurent Roland',
            '641' => 'Loit Jacques',
            '645' => 'Marostegan Bruno',
            '658' => 'Navarre Jacques',
            '670' => 'Prigent Jacques',
            '671' => 'Ptak Edouard',
            '679' => 'Sneyers Jean',
            '683' => 'Strocchio Alfred',
            '685' => 'Tarmoul Mohamed',
            '694' => 'Weissmann Rene',
            '696' => 'Zambujo Tiodmir',
        ],
        'A2' => [
            // from 576 académiciens de médecine                                                                           
            '13' => 'Arloing Fernand',
            '36' => 'Bard Louis',
            '44' => 'Baudoin Alphonse',
            '51' => 'Bechamps Pierre',
            '58' => 'Berard Leon',
            '61' => 'Bergonie Jean',
            '84' => 'Bonnet Amedee',
            '85' => 'Boquel Andre',
            '93' => 'Bougault Joseph',
            '128' => 'Carlet Gaston',
            '129' => 'Carnot Paul',
            '131' => 'Castaigne Joseph',
            '149' => 'Chassaignac Pierre',
            '162' => 'Clémenceau Georges',
            '167' => 'Colin Leon',
            '181' => 'Couvelaire Alexandre',
            '182' => 'Coyne Paul',
            '200' => 'Delepine Marcel',
            '206' => 'Demons Jean',
            '212' => 'Desbouis Guy',
            '216' => 'Deve Felix',
            '230' => 'Dubar Louis',
            '238' => 'Duguet Jean',
            '241' => 'Dumas Georges',
            '281' => 'Fredet Pierre',
            '297' => 'Gerdy Joseph',
            '310' => 'Goris Albert',
            '311' => 'Gosset Antonin',
            '350' => 'Hugounenq Louis',
            '355' => 'Jacoulet Claude',
            '360' => 'Jeambrau Emile',
            '368' => 'Juillet Armand',
            '372' => 'Kirmisson Edouard',
            '375' => 'Labbe Marcel',
            '431' => 'Lermoyez Marcel',
            '451' => 'Longet Francois',
            '466' => 'Mallat Antonin',
            '469' => 'Manquat Alexandre',
            '475' => 'Marion Jean',
            '485' => 'Masson Claude',
            '486' => 'Mathis Constant',
            '491' => 'Mauricet Alphonse',
            '499' => 'Merklen J',
            '504' => 'Meunier Henri',
            '506' => 'Mignot Antoine',
            '512' => 'Montprofit Jacques',
            '544' => 'Ollivier Auguste',
            '550' => 'Pamard Alfred',
            '586' => 'Pitres Albert',
            '615' => 'Ravaut Paul',
            '620' => 'Regis Emmanuel',
            '637' => 'Richet Charles',
            '652' => 'Rouviere Henri',
            '653' => 'Rouvillois Henri',
            '655' => 'Sabrazes Jean',
            '659' => 'Sartory Auguste',
            '662' => 'Schwartz Edouard',
            '682' => 'Spillmann Louis',
            '706' => 'Teissier Joseph',
            '699' => 'Thierry Auguste',
            '712' => 'Trebuchet Adolphe',
            //
            // from 508 autres médecins notables
            //
            '757' => 'Aymard Jean',
            '765' => 'Arraud Camille',
            '776' => 'Badolle Albert',
            '781' => 'Barbier Gaston',
            '785' => 'Barrault Jouis',
            '795' => 'Becart Auguste',
            '803' => 'Berger Jean',
            '804' => 'Bergeret Andre',
            '810' => 'Bienvenu Georges',
            '822' => 'Bonnefon Georges',
            '827' => 'Boucher Humbert',
            '836' => 'Bourret Marcel',
            '792' => 'Beal Victor',
            '841' => 'Brechot Adolphe',
            '881' => 'Chapoy Rene',
            '888' => 'Chaton Marcel',
            '903' => 'Cornet Albert',
            '904' => 'Cornet Pierre',
            '905' => 'Corret Pierre',
            '906' => 'Cosse Francois',
            '908' => 'Coste Jean',
            '910' => 'Cottenot Paul',
            '919' => 'Cresson Fortune',
            '936' => 'Delattre Raoul',
            '939' => 'Delobel Emile',
            '944' => 'Descomps Paul',
            '945' => 'Deslions Leon',
            '965' => 'Dumas Dominique',
            '966' => 'Dumas Eugene',
            '977' => 'Estradere Jean',
            '995' => 'Francais Henri',
            '1002' => 'Garcin Joseph',
            '1012' => 'Ginesty Albert',
            '1021' => 'Grandjean Alexandre',
            '1023' => 'Grasset Raymond',
            '1027' => 'Grenier Cardenal Henri',
            '1034' => 'Guillemin Joseph',
            '1041' => 'Guyon Emile',
            '1047' => 'Henry Jean',
            '1056' => 'Jacob Gustave',
            '1068' => 'Kuhn Robert',
            '1084' => 'Lassabliere Pierre',
            '1090' => 'Lebailly Charles',
            '1100' => 'Lelong Marcel',
            '1107' => 'Lepoutre Carlos',
            '1116' => 'Lonjumeau Pierre',
            '1121' => 'Lucy Andre',
            '1126' => 'Manceaux Louis',
            '1150' => 'Moiroud Pierre',
            '1155' => 'Moreau Rene',
            '1156' => 'Morel Jacques',
            '1158' => 'Morlet Antonin',
            '1170' => 'Nouel Jean',
            '1177' => 'Paschetta Charles',
            '1198' => 'Piollet Paul',
            '1220' => 'Renard Leon',
            '1247' => 'Savoire Camille',
            '1261' => 'Sikora Pierre',
            '1262' => 'Simon Clement',
            '1271' => 'Taillard Fulbert',
            '1295' => 'Verdier Pierre',
            '1300' => 'Viard Marcel',
            //
            // from "349 membres, associés et correspondants de l'académie des sciences"
            //
            '2585' => 'Bonnet Pierre',
            '2587' => 'Borel Emile',
            '2588' => 'Bornet Edouard',
            '2590' => 'Bouligand Georges',
            '2604' => 'Broglie Louis',
            '2619' => 'Charpy Augustin',
            '2621' => 'Chazy Jean',
            '2628' => 'Colin Henri',
            '2641' => 'Darboux Gaston',
            '2671' => 'Dupuy Lome Henri',
            '2677' => 'Fabry Charles',
            '2689' => 'Foch Ferdinand',
            '2696' => 'Friedel Georges',
            '2700' => 'Gasparin Paul',
            '2714' => "Grand'Eury Cyrille",
            '2717' => 'Gravier Charles',
            '2725' => 'Haag Jules',
            '2751' => 'Jumelle Henri', // this one not in list of names
            '2757' => 'Lagatu Henri',
            '2787' => 'Lesseps Ferdinand', // this one not in list of names
            '2799' => 'Maire Rene',
            '2815' => 'Maurain Charles',
            '2822' => 'Montel Paul',
            '2834' => 'Pascal Paul',
            '2866' => 'Ravaz Louis',
            '2896' => 'Stephan Edouard',
            '2933' => 'Zeiller Rene',
        ],
    ];

}// end class    
