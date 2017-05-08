<?php
/********************************************************************************
    Importation of Gauquelin 5th edition ; code specific to series A
    matches first list and chronological order list
    
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
        ex : in file 902gdA1y, profession of persons numbered between 1 and 86 is Athlétisme
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
        Asoociative array NUM => name
        Name spelling is the exact spelling contained in gd902N.html
        This exact spelling is used to remove ambiguities
    **/
    const CORRECTIONS_1955 = [
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
            // from 508 autres médecins notables
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
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
        ],
    ];
    
    
    // *****************************************
    /** 
        Parses one file of serie A and stores it in a csv file
        Merge the original list (without names) with names contained in file 902gdN.html
        So merge is done using birthdate - Merging not complete because of doublons (persons born the same day)
        @param  $serie  String identifying the serie (ex : 'A1')
        @return report
        @throws Exception if unable to parse
    **/
    public static function import($serie){
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
        $fieldnames1 = explode(Gauquelin5::SEP, $m[1]);
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Gauquelin5::SEP, $line1);
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
        // 3 - merge res1 and res2
        //
        $res = []; // contains correctly merged
        $n_ok = 0;
        $missing_in_names = [];         //
        $doublons_same_nb = [];         // multiple persons born the same day ; same nb of persons in list 1 and list 2
        $doublons_different_nb = [];    // multiple persons born the same day ; different nb of persons in list 1 and list 2
        foreach($res1 as $day1 => $array1){
            if(isset(self::CORRECTIONS_1955[$serie])){
                foreach($array1 as $tmp){
                }
            }
            if(!isset($res2[$day1])){
                // date in list 1 and not in name list
                foreach($array1 as $tmp){
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
                        $tmp['NAME'] = self::compute_name($serie, $tmp['NUM']);
                        $res[] = $tmp;
                    }
                    // fill $doublons_same_nb with all candidate lines
                    $new_doublon = [$file_serie => [], $file_names => []];
                    foreach($array1 as $tmp){
                        $new_doublon[$file_serie][] = implode("\t", $tmp);
                    }
                    foreach($array2 as $tmp){
                        $new_doublon[$file_names][] = implode("\t", $tmp);
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
            $n = count($res);
            for($i=0; $i < $n; $i++){
                $NUM = $res[$i]['NUM'];
                if(isset(self::CORRECTIONS_1955[$serie][$NUM])){
//echo "correction $NUM with " . self::CORRECTIONS_1955[$serie][$NUM] . "\n";
                    $res[$i]['NAME'] = self::CORRECTIONS_1955[$serie][$NUM];
                }
            }
        }
        //
        // report
        //
        $do_report_full = false; // @todo put in config
        $n1 = count($missing_in_names);
        $n2 = count($doublons_same_nb);
        $n3 = count($doublons_different_nb);
        $n_correction_1955 = isset(self::CORRECTIONS_1955[$serie]) ? count(self::CORRECTIONS_1955[$serie]) : 0;
        $n_bad = $n1 + $n2 + $n3 - $n_correction_1955;
        $n_ok += $n_correction_1955;
        $percent_ok = round($n_ok * 100 / count($lines1), 2);
        $report .= "nb in list1 ($file_serie) : " . count($lines1) . " - nb in list2 ($file_names) : " . count($names) . "\n";
        $report .= "case 1 : dates present in $file_serie and missing in $file_names : $n1\n";
        if($do_report_full) $report .=  print_r($missing_in_names, true) . "\n";
        $report .= "case 2 : date ambiguities with same nb : $n2\n";
        if($do_report_full) $report .= print_r($doublons_same_nb, true) . "\n";
        $report .= "case 3 : date ambiguities with different nb : $n3\n";
        if($do_report_full) $report .= print_r($doublons_different_nb, true) . "\n";
        $report .= "Corrections from 1955 book : $n_correction_1955\n";
        $report .= "total NOT ok : $n_bad\n";
        $report .= "nb OK (match without ambiguity) : $n_ok ($percent_ok %)\n";
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
            $new['COU'] = self::COUNTRIES[$cur['COU']];
            $new['COD'] = trim($cur['COD']);
            if($new['COU'] == 'FR' && $new['COD'] == 'ALG'){
                $new['COU'] = 'DZ';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'MON'){
                $new['COU'] = 'MC';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'BEL'){
                $new['COU'] = 'BE';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'B'){
                $new['COU'] = 'BE';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'SCHW'){
                $new['COU'] = 'CH';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'G'){
                $new['COU'] = 'DE';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'ESP'){
                $new['COU'] = 'ES';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'I'){
                $new['COU'] = 'IT';
                $new['COD'] = '';
            }
            else if($new['COU'] == 'FR' && $new['COD'] == 'N'){
                $new['COU'] = 'NL';
                $new['COD'] = '';
            }
            $new['LON'] = Gauquelin5::computeLg($cur['LON']);
            $new['LAT'] = Gauquelin5::computeLat($cur['LAT']);                             
            // @todo link to geonames
            $csv .= implode(Gauquelin5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dest-dir'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        return $report;
    }
    
    
    // ******************************************************
    /** 
        Computes precise profession when possible
        First compute not-detailed profession from $pro
        Then computes precise profession from $num, if possible
        Auxiliary of import()
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
        Auxiliary of import()
    **/
    private static function compute_name($serie, $num){
        return "Gauquelin-$serie-$num";
    }
    
    
}// end class    

