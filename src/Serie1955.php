<?php
/********************************************************************************
    Generation of the original groups published in Gauquelin's book in 1955
    Uses class Data1955, which is generated by tools/generate-1955.php
    The process is :
    - generate serie A csv files (php run-gauquelin5 A)
    - modify the csv file manually, adding a column named '1955'
    - generate class Data1955 with tools/generate-1955.php
    - generate the 1955 groups using this class php run-gauquelin5 1955)
    
    @license    GPL
    @history    2017-05-08 23:39:19+02:00, Thierry Graff : creation
********************************************************************************/
namespace gauquelin5;

use gauquelin5\Gauquelin5;
use gauquelin5\Serie1955Data;
use gauquelin5\init\Config;

class Serie1955{
    
    const CSV_SEP_LIBREOFFICE = ','; // grrrr, libreoffice transformed ; in ,
    
    /**
        1955 groups ; format : group code => [name, serie]
        serie is 'ZZ' for groups that can't be found in cura data
    **/
    const GROUPS_1955 = [
        '576MED' => ["576 membres associés et correspondants de l'académie de médecine", 'A2'],
        '508MED' => ['508 autres médecins notables', 'A2'],
        '570SPO' => ['570 sportifs', 'A1'],
        '676MIL' => ['676 militaires', 'A3'],
        '906PEI' => ['906 peintres', 'A4'],
        '361PEI' => ['361 peintres mineurs', 'ZZ'],
        '500ACT' => ['500 acteurs', 'A5'],
        '494DEP' => ['494 députés', 'A5'],
        '349SCI' => ["349 membres, associés et correspondants de l'académie des sciences", 'A2'],
        '884PRE' => ['884 prêtres', 'ZZ'],
    ];

    
    // *****************************************
    /** 
        Generates the csv files in 6-1955-final/ from csv files located in 5-1955-cura-corrected/
        See 6-1955-final/README for a meaning of generated fields
        
        Called by : php run-gauquelin5.php 1955 finalize
        
        @param  $serie  String must be '1955' - useless but kept for conformity with other classes
        @return report
        @throws Exception if unable to parse
    **/
    public static function corrected2final($serie){
        $src_dir = Config::$data['dirs']['5-1955-cura-corrected'];
        $dest_dir = Config::$data['dirs']['6-1955-final'];
        $files = glob($src_dir . DS . '*.csv');
        $generatedFields = [
            'ORIGIN',
            'NUM',
            'FIRSTNAME',
            'LASTNAME',
            'PRO',
            'DATE',
            'PLACE',
            'COU',
            'ADM2',
            'LON',
            'LAT',
        ];
        if(Config::$data['dirs']['3-cura-marked']){
            $generatedFields[] = 'GEONAMEID';
        }
        $firstline = implode(Gauquelin5::CSV_SEP, $generatedFields);
        foreach($files as $file){
            $res = $firstline . "\n";
            $groupCode = str_replace('.csv', '', basename($file));
            $lines = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
            $fieldnames = explode(self::CSV_SEP_LIBREOFFICE, $lines[0]);
//echo "\n<pre>"; print_r(explode(self::CSV_SEP_LIBREOFFICE, $lines[0])); echo "</pre>"; exit;
            array_shift($lines); // line containing field names
            foreach($lines as $line){
                $new = [
                    'ORIGIN' => '',
                    'NUM' => '',
                    'SLUG' => '',
                    'FAMILYNAME' => '',
                    'GIVENNAME' => '',
                    'PRO' => '',
                    'BIRTHDATE' => '',
                    'BIRTHPLACE' => '',
                    'COU' => '',
                    'ADM2' => '',
                    'GEOID' => '',
                    'LG' => '',
                    'LAT' => '',
                    
                ];
                // turn $fields to be [ 'ORIGIN' => 'A1', 'NUM' => '6', 'NAME' => 'Bally Etienne', ...]
// @todo externalize this code
                $tmp = explode(self::CSV_SEP_LIBREOFFICE, $line);
                foreach($fieldnames as $k => $v){
                    $fields[$v] = $tmp[$k];
                }
                //
                $new['ORIGIN'] = $fields['ORIGIN'];
                $new['NUM'] = $fields['NUM'];
                // name
                $tmp = explode(' ', $fields['NAME']);
                if(count($tmp) != 2){
                    // can happen for 2 cases :
                    // - persons not in cura files and added by a human
                    // - persons with composed last names
                    // In both cases, FIRST_C and LAST_C should be filled
                    if($fields['FIRST_C'] == '' || $fields['LAST_C'] == ''){
                        echo "ANOMALY ON NAMES - {$new['NUM']} - LINE SKIPPED, MUST BE FIXED\n";
                        continue;
                    }
                    else{
                        $family = $fields['FIRST_C'];
                        $given = $fields['LAST_C'];
                    }
                }
                else{
                    [$family, $given] = explode(' ', $fields['NAME']); // ex 'Bally Etienne'
                    // If FIRST_C or LAST_C are filled, override cura value
                    if($fields['FIRST_C'] != ''){
                        $family = $fields['FIRST_C'];
                    }
                    if($fields['LAST_C'] != ''){
                        $given = $fields['LAST_C'];
                    }
                }
                $new['FAMILYNAME'] = $family;
                $new['GIVENNAME'] = $given;
                // profession
                if($fields['PRO_C'] != ''){
                    $new['PRO'] = $fields['PRO_C'];
                }
                else{
                    $new['PRO'] = $fields['PRO'];
                }
                //
                // place
                // processed before date to find timezone from geonames
                // but date is put before place in resulting line
                if($fields['PLACE_C'] != ''){
                    $place = $fields['PLACE_C'];
                }
                else{
                    $place = $fields['PLACE'];
                }
                if($fields['COU_C'] != ''){
                    $country = $fields['COU_C'];
                }
                else{
                    $country = $fields['COU'];
                }
                if($fields['COD_C'] != ''){
                    $admin2 = $fields['COD_C'];
                }
                else{
                    $admin2 = $fields['COD_C'];
                }
                if($admin2 == 'NONE'){
                    $admin2 = '';
                }
                if(strlen($admin2) == 1 && $country == 'FR'){
                    $admin2 = '0' . $admin2; // because libreoffice "eats" the trailing 0
                }
                // HERE try to match Geonames
                $slug = \lib::slugify($place);
                $geonames = \Geonames::match([
                    'slug' => $slug,
                    'countries' => [$country],
                    'admin2-code' => $admin2,
                ]);
                if(!$geonames){
                    echo "ERROR: COULD NOT MATCH GEONAMES - {$new['NUM']} - $slug $admin2 - LINE SKIPPED, MUST BE FIXED\n";
                    continue;
                }
                $new['SLUG']        = $geonames['slug'];
                $new['BIRTHPLACE']  = $geonames['name'];
                $new['GEOID']       = $geonames['geoid'];
                $new['LG']          = $geonames['longitude'];
                $new['LAT']         = $geonames['latitude'];
                $new['ADM2'] = $admin2;
                $new['COU'] = $country;
                //
                // birth date
                //
                $dtu = TZUtils::spacetime2dtu($geonames['timezone'], $date);
                
                
/* 
    [0] => G55
    [1] => ORIGIN
    [2] => NUM
    [3] => NAME
    [4] => PRO
    [5] => DATE
    [6] => PLACE
    [7] => COU
    [8] => COD
    [9] => LON
    [10] => LAT
    [11] => FIRST_C
    [12] => LAST_C
    [13] => HOUR_C
    [14] => DAY_C
    [15] => PLACE_C
    [16] => COD_C
    [17] => COU_C
    [18] => NOTES_C
    [19] => PRO_C
*/
/* 
                $new = [
                    //'ORIGIN' => '',
                    //'NUM' => '',
                    //'SLUG' => '',
                    //'FAMILYNAME' => '',
                    //'GIVENNAME' => '',
                    //'PRO' => '',
                    'BIRTHDATE' => '',
                    //'BIRTHPLACE' => '',
                    //'COU' => '',
                    //'ADM2' => '',
                    //'GEOID' => '',
                    //'LG' => '',
                    //'LAT' => '',
                ];
*/
/* 
echo "\n<pre>"; print_r($geonames); echo "</pre>"; exit;
            [geoid] => 2970072
            [name] => Vénissieux
            [slug] => venissieux
            [admin2_code] => 69
            [longitude] => 4.87147
            [latitude] => 45.70254
            [timezone] => Europe/Paris
*/

                exit;
            }
        }
    }
    
    // *****************************************
    /** 
        Generates the 1955 files in 4-1955-generated/ from :
        - csv files located in 2-cura-exported/
        - csv files located in 3-cura-marked/
        Takes an exact copy of files in 2-cura-exported
        Uses files from 3-cura-marked to filter and dispatch in different resulting files
        Adds a column ORIGIN
        
        Called by : php run-gauquelin5.php 1955 marked21955
        
        @param  $serie  String must be '1955' - useless but kept for conformity with other classes
        @return report
        @throws Exception if unable to parse
    **/
    public static function marked21955($serie){
        $src_dir = Config::$data['dirs']['3-cura-marked'];
        $dest_dir = Config::$data['dirs']['4-1955-generated'];
        
        $groups55 = self::loadGroups3($src_dir);
        
        foreach(self::GROUPS_1955 as $groupCode => [$groupName, $serie]){
            if(count($groups55[$groupCode]) == 0){
                continue; // for groups not treated yet
            }
            echo "Generating 1955 group $groupCode : $groupName\n";
            $res = [];
            $inputfile = Config::$data['dirs']['2-cura-exported'] . DS . $serie . '.csv'; // file generated by class SerieA
            $input = file($inputfile);
            $N = count($input);
            $fieldnames = explode(Gauquelin5::CSV_SEP, $input[0]);
            for($i=1; $i < $N; $i++){
                $fields = explode(Gauquelin5::CSV_SEP, $input[$i]);
                $NUM = $fields[0]; // by convention, all generated csv file of 2-cura-exported have NUM as first field
                if(!in_array($NUM, $groups55[$groupCode])){
                    continue;
                }
                $res[] = $fields;
            }
            //
            // sort $res
            //
            // here simplification : files 2-cura-exported/A1.csv and A2.csv
            // have first field = NUM and second field = NAME
            $sort_field = (Config::$data['1955']['sort'][$groupCode] == 'NUM' ? 0 : 1);
            $res = \lib::sortByKey($res, $sort_field);
            echo '  ' . count($res) . " persons stored\n";
            // generate output
            $output = 'ORIGIN' . Gauquelin5::CSV_SEP . $input[0]; // field names
            foreach($res as $fields){
                $output .= $serie . Gauquelin5::CSV_SEP . implode(Gauquelin5::CSV_SEP, $fields);
            }
            $dest_file = $dest_dir . DS . $groupCode . '.csv';
            file_put_contents($dest_file, $output);
        }
    }
    
    
    // ******************************************************
    /**
        Loads the csv files located in 3-cura-marked/ in arrays
        Auxiliary of self::marked21955()
        @param $src_dir String Directory called 3-cura-marked/ in config
        @return associative array :
                group code => array containing the values of NUM in this group
                group codes are keys of self::GROUPS_1955
    **/
    private static function loadGroups3($src_dir){
        $res = [];
        foreach(self::GROUPS_1955 as $groupCode => [$name, $serie]){
            $res[$groupCode] = [];
            $raw = @file_get_contents($src_dir . DS . $serie . '.csv');
            if($raw === false){
                continue;
            }
            $raw = str_replace('"', '', $raw); // libreoffice adds " and I don't know how to remove them
            $lines = explode("\n", $raw);
            $nlines = count($lines);
            $fieldnames = explode(self::CSV_SEP_LIBREOFFICE, $lines[0]);
            $flip = array_flip($fieldnames);
            for($i=1; $i < $nlines; $i++){
                if(trim($lines[$i]) == ''){
                    continue;
                }
                $fields = explode(self::CSV_SEP_LIBREOFFICE, $lines[$i]);
                $code = $fields[$flip['1955']];
                if($code != $groupCode){
                    continue;
                }
                $res[$groupCode][] = $fields[$flip['NUM']];
            }
        }
        return $res;
    }
    
}// end class    

