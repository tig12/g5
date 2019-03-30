<?php
/********************************************************************************
    Importation of Gauquelin 5th edition.
    Code specific to series A.
    Matches first list and chronological order list
    
    This code uses file 902gdN.html to retrieve the names, but this could have been done using only 902gdA*y.html files
    (for example, 902gdA1y.html could have been used instead of using 902gdA1.html and 902gdN.html).
    
    @license    GPL
    @history    2017-04-27 10:53:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace gauquelin5;

use gauquelin5\Gauquelin5;
use gauquelin5\Names;
use gauquelin5\init\Config;

class SerieA{
    
    /**
        Associations between profession codes used in the cura html files
        and profession codes used in the generated csv files
        for the different files of Series A.
        These associations are used when no further details are provided
    **/
    const PROFESSIONS_NO_DETAILS = [
        'A1' => ['C' => 'SP'],
        'A2' => ['S' => 'SC'],
        'A3' => ['M' => 'MI'],
        'A4' => ['P' => 'PAI', 'M' => 'MUS'],
        'A5' => ['A' => 'ACT', 'PT' => 'PO'],
        'A6' => ['W' => 'WR', 'J' => 'JO'], 
    ];
    
    /** 
        More detailed professions
        ex : in file 902gdA1y, profession of persons numbered between 1 and 86 (inclusive) is Athlétisme
    **/
    const PROFESSIONS_DETAILS = [
        'A1' => [
            ['ATH', 1, 86],
            ['AUT', 87, 122],
            ['AVI', 123, 514],
            ['AVR', 515, 522],
            ['BAS', 523, 555],
            ['BIL', 556, 564],
            ['BOX', 565, 768],
            ['CAN', 769, 769],
            ['CYC', 770, 1226],
            ['ESC', 1227, 1242],
            ['FOO', 1243, 1690],
            ['GOL', 1691, 1698],
            ['GYM', 1699, 1710],
            ['HAL', 1711, 1726],
            ['HAN', 1727, 1730],
            ['HOC', 1731, 1741],
            ['LUT', 1742, 1751],
            ['MAR', 1752, 1757],
            ['NAT', 1758, 1784],
            ['PEL', 1785, 1802],
            ['RUG', 1803, 2009],
            ['SKI', 2010, 2026],
            ['EQU', 2027, 2037],
            ['GLA', 2038, 2040],
            ['TEN', 2041, 2075],
            ['TIR', 2076, 2085],
            ['VOI', 2086, 2088],
            ['VOL', 2089, 2089],
        ],
        'A2' => [
            ['PH', 1, 2552],
            ['SC', 2553, 3647], 
        ],
        // nothing for A3
        'A4' => [
            ['AR', 1, 1473],
            ['MUS', 1474, 2339],
            ['CMB', 2340, 2722] 
        ],
        'A5' => [
            ['ACT', 1, 1409],
            ['PO', 1410, 2412],
        ],
        // nothing for a6
    ];

    /** Mapping from country codes to iso3166 codes ; applies to all files of Series A **/
    const COUNTRIES = [
        'F' => 'FR',
        'I' => 'IT',
        'G' => 'DE',
        'B' => 'BE',
        'N' => 'NL',
        'S' => 'CH',
    ];
    
    /** 
        Manual corrections : name matching added using lists published by Gauquelin in 1955
        Asoociative array : [
                serie => [
                    NUM => name,
                    ...
                ],
                ...
            ]
        Name spelling is the exact spelling contained in gd902N.html
        This exact spelling is used to remove ambiguities
        It may differ from 1955 book spelling
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
    
    
    // *****************************************
    /** 
        Parses one html cura file of serie A (locally stored in directory 1-cura-raw/)
        and stores it in a csv file (in directory 2-cura-exported/)
        
        Merges the original list (without names) with names contained in file 902gdN.html
        So merge is done using birthdate.
        Merge is not complete because of doublons (persons born the same day).
        
        @param  $serie  String identifying the serie (ex : 'A1')
        @return report
        @throws Exception if unable to parse
    **/
    public static function raw2exported($serie){
        $report =  "--- Importing serie $serie\n";
        $raw = Gauquelin5::readHtmlFile($serie);
        $file_serie = Gauquelin5::serie2filename($serie);
        $file_names = Gauquelin5::serie2filename(Names::SERIE);
        //
        // 1 - parse first list (without names) - store by birth date to prepare matching
        //
        $res1 = [];
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse first list (without names) in " . $file_serie);
        }
        $fieldnames1 = explode(Gauquelin5::HTML_SEP, $m[1]);
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Gauquelin5::HTML_SEP, $line1);
            $tmp = [];
            for($i=0; $i < count($fields); $i++){
                $tmp[$fieldnames1[$i]] = $fields[$i]; // ex: $tmp['YEA'] = '1817'
            }
            $day = Gauquelin5::computeDay($tmp);
            if(!isset($res1[$day])){
                $res1[$day] = [];
            }
            $res1[$day][] = $tmp;
        }
        //
        // 2 - prepare names - store by birth date to prepare matching
        //
        $res2 = [];
        $names = Names::parse()[$serie];
        foreach($names as $fields){
            $day = $fields['day'];
            if(!isset($res2[$day])){
                $res2[$day] = [];
            }
            $res2[$day][] = $fields;
        }
        //
        // 3 - merge res1 and res2 (name list)
        //
        $res = [];
        // variables used only for report
        $n_ok = 0;                                  // correctly merged
        $n1 = 0; $missing_in_names = [];        // date present in list 1 and not in name list
        $n2 = 0; $doublons_same_nb = [];        // multiple persons born the same day ; same nb of persons in list 1 and name list
        $n3 = 0; $doublons_different_nb = [];   // multiple persons born the same day ; different nb of persons in list 1 and name list
        foreach($res1 as $day1 => $array1){
            if(!isset($res2[$day1])){
                // date in list 1 and not in name list
                foreach($array1 as $tmp){
                    $n1++;
                    $missing_in_names[] = [
                        'LINE' => implode("\t", $tmp),
                        'NUM' => $tmp['NUM'],
                    ];
                    // store in $res with fabricated name
                    $tmp['NAME'] = self::compute_name($serie, $tmp['NUM']);
                    $res[] = $tmp;
                }
                continue;
            }
            $array2 = $res2[$day1];
            if(count($array2) != count($array1)){
                // date both in list 1 and in name list
                // but different nb of elements => ambiguity
                // store in $res with fabricated name
                foreach($array1 as $tmp){
                    $n3++;
                    $tmp['NAME'] = self::compute_name($serie, $tmp['NUM']);
                    $res[] = $tmp;
                }
                $new_doublon = [$file_serie => [], $file_names => []];
                foreach($array1 as $tmp){
                    $new_doublon[$file_serie][] = [
                        'LINE' => implode("\t", $tmp),
                        'NUM' => $tmp['NUM'],
                    ];
                }
                foreach($array2 as $tmp){
                    $new_doublon[$file_names][] = [
                        'LINE' => implode("\t", $tmp),
                        'NAME' => $tmp['name'],
                    ];
                }
                $doublons_different_nb[] = $new_doublon;
                continue;
            }
            else{
                // $array1 and $array2 have the same nb of elements
                if(count($array1) == 1){
                    // OK no ambiguity => add to res
                    $tmp = $array1[0];
                    $tmp['NAME'] = $array2[0]['name'];
                    $res[] = $tmp;
                    $n_ok++;
                }
                else{
                    // more than one persons share the same birth date => ambiguity
                    // store in $res with fabricated name
                    foreach($array1 as $tmp){
                        $n2++;
                        $tmp['NAME'] = self::compute_name($serie, $tmp['NUM']);
                        $res[] = $tmp;
                    }
                    // fill $doublons_same_nb with all candidate lines
                    $new_doublon = [$file_serie => [], $file_names => []];
                    foreach($array1 as $tmp){
                        $new_doublon[$file_serie][] = [
                            'LINE' => implode("\t", $tmp),
                            'NUM' => $tmp['NUM'],
                        ];
                    }
                    foreach($array2 as $tmp){
                        $new_doublon[$file_names][] = [
                            'LINE' => implode("\t", $tmp),
                            'NAME' => $tmp['name'],
                        ];
                    }
                    $doublons_same_nb[] = $new_doublon;
                }
            }
        }
        $res = \lib::sortByKey($res, 'NUM');
        //
        // 1955 corrections
        //
        if(isset(self::CORRECTIONS_1955[$serie])){
            [$n_ok_fix, $n1_fix, $n2_fix] = self::corrections1955($res, $missing_in_names, $doublons_same_nb, $serie, $file_serie, $file_names);
        }
        else{
            $n_ok_fix = $n1_fix = $n2_fix = 0;
        }
        //
        // report
        //
        $do_report_full = false; // @todo put in config
        $n_correction_1955 = isset(self::CORRECTIONS_1955[$serie]) ? count(self::CORRECTIONS_1955[$serie]) : 0;
        $n_bad = $n1 + $n2 + $n3 - $n_ok_fix - $n1_fix - $n2_fix;
        $n_good = $n_ok + $n_ok_fix + $n1_fix + $n2_fix;
        $percent_ok = round($n_good * 100 / count($lines1), 2);
        $percent_not_ok = round($n_bad * 100 / count($lines1), 2);
        $report .= "nb in list1 ($file_serie) : " . count($lines1) . " - nb in list2 ($file_names) : " . count($names) . "\n";
        $report .= "case 1 : $n1 dates present in $file_serie and missing in $file_names - $n1_fix fixed by 1955\n";
        if($do_report_full) $report .=  print_r($missing_in_names, true) . "\n";
        $report .= "case 2 : $n2 date ambiguities with same nb - $n2_fix fixed by 1955\n";
        if($do_report_full) $report .= print_r($doublons_same_nb, true) . "\n";
        $report .= "case 3 : $n3 date ambiguities with different nb\n";
        if($do_report_full) $report .= print_r($doublons_different_nb, true) . "\n";
        $report .= "Corrections from 1955 book : $n_correction_1955\n";
        $report .= "nb OK (match without ambiguity) : $n_good ($percent_ok %)\n";
        $report .= "nb NOT OK : $n_bad ($percent_not_ok %)\n";
        //
        // 4 - store result
        //
        $nb_stored = 0;
        $csv = '';
        // fields in the resulting csv
        $fieldnames = [
            'NUM',
            'NAME',
            'PRO',
            'DATE',
            'PLACE',
            'COU',
            'COD',
            'LON',
            'LAT',
        ];
        $csv = implode(Gauquelin5::CSV_SEP, $fieldnames) . "\n";
        foreach($res as $cur){
            $new = [];
            $new['NUM'] = trim($cur['NUM']);
            $new['NAME'] = trim($cur['NAME']);
            $new['PRO'] = self::compute_profession($serie, $cur['PRO'], $new['NUM']);
            // date time
            $day = Gauquelin5::computeDay($cur);
            $hour = Gauquelin5::computeHour($cur);
            $TZ = trim($cur['TZ']);
            if($TZ != 0 && $TZ != -1){
                throw new \Exception("timezone not handled : $TZ");
            }
            $timezone = $TZ == 0 ? '+00:00' : '-01:00';
            $new['DATE'] = "$day $hour$timezone";
            // place
            $new['PLACE'] = trim($cur['CITY']);
            [$new['COU'], $new['COD']] = self::compute_country($cur['COU'], $cur['COD']);
            $new['LON'] = Gauquelin5::computeLg($cur['LON']);
            $new['LAT'] = Gauquelin5::computeLat($cur['LAT']);                             
            $csv .= implode(Gauquelin5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dirs']['2-cura-exported'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= "Stored result in $csvfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2exported()
        @return [$n_ok_fix, $n1_fix, $n2_fix]
    **/
    private static function corrections1955(&$res, &$missing_in_names, &$doublons_same_nb, $serie, $file_serie, $file_names){
        $n_ok_fix = $n1_fix = $n2_fix = 0;
        //
        // Remove cases in $missing_in_names solved by 1955
        // useful only for report
        // computes $n1_fix
        //
        for($i=0; $i < count($missing_in_names); $i++){
            if(in_array($missing_in_names[$i]['NUM'], self::CORRECTIONS_1955[$serie])){
                unset($missing_in_names[$i]);
                $n1_fix++;
            }
        }
        //
        // Resolve doublons
        // computes $n2_fix
        //
        $NUMS_1955 = array_keys(self::CORRECTIONS_1955[$serie]);
        $N_DOUBLONS = count($doublons_same_nb);
        for($i=0; $i < $N_DOUBLONS; $i++){
            if(count($doublons_same_nb[$i][$file_serie]) != 2){
                // resolution works only for doublons (not triplets or more elements)
                continue;
            }
            $found = false;
            if(in_array($doublons_same_nb[$i][$file_serie][0]['NUM'], $NUMS_1955)){
                $NUM = $doublons_same_nb[$i][$file_serie][0]['NUM'];
                $NAME = self::CORRECTIONS_1955[$serie][$NUM];
                $found = 0;
            }
            else if(in_array($doublons_same_nb[$i][$file_serie][1]['NUM'], $NUMS_1955)){
                $NUM = $doublons_same_nb[$i][$file_serie][1]['NUM'];
                $NAME = self::CORRECTIONS_1955[$serie][$NUM];
                $found = 1;
            }
            if($found !== false){
                // resolve first
                $idx_num = ($found === 0 ? 1 : 0);
                $idx_name = ($doublons_same_nb[$i][$file_names][0]['NAME'] == $NAME ? 1 : 0); // HERE use of exact name spelling in self::CORRECTIONS_1955
                $new_num1 = $doublons_same_nb[$i][$file_serie][$idx_num]['NUM'];
                $new_name1 = $doublons_same_nb[$i][$file_names][$idx_name]['NAME'];
                // inject doublon resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num1){
                        $res[$j]['NAME'] = $new_name1;
                        break;
                    }
                }
                // resolve second
                $idx_num = ($idx_num == 0 ? 1 : 0);
                $idx_name = ($idx_name == 0 ? 1 : 0);
                $new_num2 = $doublons_same_nb[$i][$file_serie][$idx_num]['NUM'];
                $new_name2 = $doublons_same_nb[$i][$file_names][$idx_name]['NAME'];
                // inject doublon resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num2){
                        $res[$j]['NAME'] = $new_name2;
                        break;
                    }
                }
                $n2_fix += 2;
                unset($doublons_same_nb[$i]); // useful only for report
            }
        }
        //
        // directly fix the result with data of self::CORRECTIONS_1955
        // only for cases not solved by doublons
        // computes $n_ok_fix
        //
        $n = count($res);
        for($i=0; $i < $n; $i++){
            $NUM = $res[$i]['NUM'];
            // test on strpos done to avoid counting cases solved by doublons
            if(isset(self::CORRECTIONS_1955[$serie][$NUM]) && strpos($res[$i]['NAME'], 'Gauquelin-') === 0){
                $n_ok_fix++;
                $res[$i]['NAME'] = self::CORRECTIONS_1955[$serie][$NUM];
            }
        }
        return [$n_ok_fix, $n1_fix, $n2_fix];
    }
    
    
    // ******************************************************
    /** 
        Computes precise profession when possible
        First compute not-detailed profession from $pro
        Then computes precise profession from $num, if possible
        Auxiliary of raw2exported()
    **/
    private static function compute_profession($serie, $pro, $num){
        $res = self::PROFESSIONS_NO_DETAILS[$serie][$pro];
        if(isset(self::PROFESSIONS_DETAILS[$serie])){
            $tmp = self::PROFESSIONS_DETAILS[$serie];
            foreach($tmp as $elts){
                // $elts looks like that : ['Athlétisme', 1, 86],
                if($num >= $elts[1] && $num <= $elts[2]){
                    $res = $elts[0];
                    break;
                }
            }
        }
        return $res;
    }
    
    
    // ******************************************************
    /** 
        Computes missing names
        Auxiliary of raw2exported()
    **/
    private static function compute_name($serie, $num){
        return "Gauquelin-$serie-$num";
    }
    
    
    // ******************************************************
    /** 
        Computes the ISO 3166 country code from fields COU and COD of cura files
        Auxiliary of raw2exported()
    **/
    private static function compute_country($COU, $COD){
        $COU = self::COUNTRIES[$COU];
        $COD = trim($COD);
        if($COU == 'FR' && $COD== 'ALG'){
            $COU = 'DZ';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'MON'){
            $COU = 'MC';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'BEL'){
            $COU = 'BE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'B'){
            $COU = 'BE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'SCHW'){
            $COU = 'CH';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'G'){
            $COU = 'DE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'ESP'){
            $COU = 'ES';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'I'){
            $COU = 'IT';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'N'){
            $COU = 'NL';
            $COD= '';
        }
        return [$COU, $COD];
    }
    
    
}// end class    

