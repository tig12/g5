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
            ['Conductors of military band', 2340, 2722] 
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
    
    // *****************************************
    /** 
        Parses one file of serie A and stores it in a csv file
        Merge the original list (without names) with names contained in file 902gdN.html
        So merge is done using birthdate - Merging not complete because of doublons (persons born the same day)
        @param  $serie  String identifying the serie ('A1')
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
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse first list (without names) in " . $file_serie);
        }
        $fieldnames1 = explode(Gauquelin5::SEP, $m[1]);
        $lines1 = explode("\n", $m[2]);
        $res1 = [];
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
        $names = Names::parse()[$serie];
        $res2 = [];
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
        $res = []; // merge ok
        $n_ok = 0;
        $missing_in_names = [];         //
        $doublons_same_nb = [];         // multiple persons born the same day ; same nb of persons in list 1 and list 2
        $doublons_different_nb = [];    // multiple persons born the same day ; different nb of persons in list 1 and list 2
        foreach($res1 as $day1 => $array1){
            if(!isset($res2[$day1])){
                // date in list 1 and not in name list
                foreach($array1 as $tmp){
                    $missing_in_names[] = implode("\t", $tmp);
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
                    $new_doublon[$file_serie][] = implode("\t", $tmp);
                }
                foreach($array2 as $tmp){
                    $new_doublon[$file_names][] = implode("\t", $tmp);
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
        $n1 = count($missing_in_names);
        $n2 = count($doublons_same_nb);
        $n3 = count($doublons_different_nb);
        $n_bad = $n1 + $n2 + $n3;
        $percent_ok = round($n_ok * 100 / count($lines1), 2);
        $report .= "nb in list1 ($file_serie) : " . count($lines1) . " - nb in list2 ($file_names) : " . count($names) . "\n";
        $report .= "case 1 : dates present in $file_serie and missing in $file_names : $n1\n";
        //$report .=  print_r($missing_in_names, true) . "\n";
        $report .= "case 2 : date ambiguities with same nb : $n2\n";
        //$report .= print_r($doublons_same_nb, true) . "\n";
        $report .= "case 3 : date ambiguities with different nb : $n3\n";
        //$report .= print_r($doublons_different_nb, true) . "\n";
        $report .= "total NOT ok : $n_bad\n";
        $report .= "nb OK (match without ambiguity) : $n_ok ($percent_ok %)\n";
//return $report;
        //
        // 4 - store result
        //
        $nb_stored = 0;
        $csv = '';
        // fields in the resulting csv
        $fieldnames = [
            'NUM',
            'NAME',
            'DATE',
            'PLACE',
            'COU',
            'COD',
            'LON',
            'LAT',
            'PRO',
        ];
        $csv = implode(Gauquelin5::CSV_SEP, $fieldnames) . "\n";
        foreach($res as $cur){
            $new = [];
            $new['NUM'] = trim($cur['NUM']);
            $new['NAME'] = trim($cur['NAME']);
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
            $new['LON'] = Gauquelin5::computeLg($cur['LON']);
            $new['LAT'] = Gauquelin5::computeLat($cur['LAT']);
            // @todo link to geonames
            $new['PRO'] = self::compute_profession($serie, $cur['PRO'], $new['NUM']);
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

