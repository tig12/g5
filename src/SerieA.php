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
        Associations between profession codes and profession names for the different files of Series A
        These associations are used when no further details are provided
    **/
    const PROFESSIONS_NO_DETAILS = [
        'A1' => ['C' => 'Sport Champion'],
        'A2' => ['S' => 'Scientist'],
        'A3' => ['M' => 'Military man'],
        'A4' => ['P' => 'Painter', 'M' => 'Musician'],
        'A5' => ['A' => 'Actor', 'PT' => 'Politician'],
        'A6' => ['W' => 'Writer', 'J' => 'Journalist'], 
    ];
    
    /** 
        More detailed professions
        ex : in file 902gdA1y, profession of persons numbered between 1 and 86 is Athlétisme
    **/
    const PROFESSIONS_DETAILS = [
        'A1' => [
            ['Athlétisme', 1, 86],
            ['Auto-moto', 87, 122],
            ['Avion (Aviation)', 123, 514],
            ['Aviron (Rowing)', 515, 522],
            ['Basketball', 523, 555],
            ['Billard', 556, 564],
            ['Boxe', 565, 768],
            ['Canoe-kayak', 769, 769],
            ['Cyclisme', 770, 1226],
            ['Escrime (Fencing)', 1227, 1242],
            ['Football', 1243, 1690],
            ['Golf', 1691, 1698],
            ['Gymnastique', 1699, 1710],
            ['Haltérophilie (Weightlifting)', 1711, 1726],
            ['Handball', 1727, 1730],
            ['Hockey', 1731, 1741],
            ['Lutte (Wrestling)', 1742, 1751],
            ['Marche (Walking)', 1752, 1757],
            ['Natation (Swimming)', 1758, 1784],
            ['Pelote basque', 1785, 1802],
            ['Rugby et Jeu à XIII', 1803, 2009],
            ['Ski', 2010, 2026],
            ['Sports équestres (Equestrian)', 2027, 2037],
            ['Sports de glace (Bobsleigh and Skating)', 2038, 2040],
            ['Tennis', 2041, 2075],
            ['Tir (Shooting)', 2076, 2085],
            ['Voile (Sailing)', 2086, 2088],
            ['Volley ball', 2089, 2089],
        ],
        'A2' => [
            ['Physician', 1, 2552],
            ['Scientist', 2553, 3647], 
        ],
        // nothing for A3
        'A4' => [
            ['Painters, and some architects, engravers, sculptors, etc', 1, 1473],
            ['Musician', 1474, 2339],
            ['Conductors of military band', 2340, 2722] 
        ],
        'A5' => [
            ['Actor', 1, 1409],
            ['Politician', 1410, 2412],
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
        $missing_in_names = [];
        $doublons_same_nb = [];         // multiple persons born the same day ; same nb of persons in list 1 and list 2
        $doublons_different_nb = [];    // multiple persons born the same day ; different nb of persons in list 1 and list 2
        foreach($res1 as $day1 => $array1){
            if(!isset($res2[$day1])){
                // date in list 1 and not in name list
                foreach($array1 as $tmp){
                    $missing_in_names[] = implode("\t", $tmp);
                    // store in $res with fabricated name
                    $tmp['NAME'] = "Gauquelin-$serie " . $tmp['NUM'];
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
                    $tmp['NAME'] = "gauquelin-$serie " . $tmp['NUM'];
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
                }
                else{
                    // more than one persons share the same birth date => ambiguity
                    // store in $res with fabricated name
                    foreach($array1 as $tmp){
                        $tmp['NAME'] = "gauquelin-$serie " . $tmp['NUM'];
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
        $report .= "nb in $file_serie : " . count($lines1) . "\nnb in $file_names : " . count($names) . "\n";
        $report .= "Dates missing in $file_names : " . count($missing_in_names) . "\n";
        //$report .=  print_r($missing_in_names, true) . "\n";
        $report .= "Date ambiguities with same nb : " . count($doublons_same_nb) . "\n";
        //$report .= print_r($doublons_same_nb, true) . "\n";
        $report .= "Date ambiguities with different nb : " . count($doublons_different_nb) . "\n";
        //$report .= print_r($doublons_different_nb, true) . "\n";
        $report .= "nb OK (match without ambiguity) : " . count($res) . "\n";
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
                throw new Exception("timezone not handled : $TZ");
            }
            $timezone = $TZ == 0 ? '+00:00' : '-01:00';
            $new['DATE'] = "$day $hour$timezone";
            // place
            $new['PLACE'] = trim($cur['CITY']);
            $new['COU'] = self::COUNTRIES[$cur['COU']];
            $new['COD'] = trim($cur['COD']);
            $new['LON'] = Gauquelin5::computeLg($cur['LON']);
            $new['LAT'] = Gauquelin5::computeLat($cur['LAT']);
            // @todo link to geonames
            $new['PRO'] = self::compute_profession($serie, $cur['PRO'], $new['NUM']);
            $csv .= implode(Gauquelin5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dest-dir'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /** 
        Compute precise profession when possible
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
    
    
}// end class    

