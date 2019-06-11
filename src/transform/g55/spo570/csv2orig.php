<?php
/********************************************************************************
    Generates 9-output/gauquelin55/original/570SPO.csv
    
    @license    GPL
    @history    2019-05-26 00:48:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\g55\spo570;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\strings\slugify;
use tiglib\timezone\offset;
use tiglib\timezone\offset_fr;

class csv2orig implements Command {
    
    // *****************************************
    /** 
        @param $param Array containing one element (a string)
        @return report
    **/
    public static function execute($params=[]): string{
    }
    
    // *****************************************
    /** 
        Generates the csv files in 9-g55-original/ from csv files located in 3-g55-edited/
        See 9-g55-original/README for a meaning of generated fields
        
        Called by : php run-gauquelin5.php 1955 finalize
        
        @param  $serie  String must be '1955' - useless but kept for conformity with other classes
        @return report
        @throws Exception if unable to parse
    **/
    public static function generateOriginal($serie){
        $src_dir = Config::$data['dirs']['3-g55-edited'];
        $dest_dir = Config::$data['dirs']['9-g55-original'];
        $files = glob($src_dir . DS . '*.csv');
        $generatedFields = [
            'ORIGIN' => '',
            'NUM' => '',
            'FAMILYNAME' => '',
            'GIVENNAME' => '',
            'OCCU' => '',
            'BIRTHDATE' => '',
            'BIRTHPLACE' => '',
            'COU' => '',
            'ADM2' => '',
            'GEOID' => '',
            'LG' => '',
            'LAT' => '',
//            'COMMENT' => '',
        ];
        $firstline = implode(G5::CSV_SEP, array_keys($generatedFields));
        foreach($files as $file){
            $res = $firstline . "\n";
            $groupCode = str_replace('.csv', '', basename($file));
            $lines = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
            $fieldnames = explode(self::CSV_SEP_LIBREOFFICE, $lines[0]);
            array_shift($lines); // line containing field names
            foreach($lines as $line){
                $new = $generatedFields;
                // turn $fields to be [ 'ORIGIN' => 'A1', 'NUM' => '6', 'NAME' => 'Bally Etienne', ...]
// @todo externalize this code
                $tmp = explode(self::CSV_SEP_LIBREOFFICE, $line);
                foreach($fieldnames as $k => $v){
                    $fields[$v] = $tmp[$k];
                }
// end @todo externalize this code
                //
                $new['ORIGIN'] = $fields['ORIGIN'];
                $new['NUM'] = $fields['NUM'];
                // name
                $tmp = explode(' ', $fields['NAME']);
                if(count($tmp) != 2){
                    // can happen for 2 cases :
                    // - persons not in cura files and added by a human
                    // - persons with composed last names
                    // In both cases, GIVEN_C and FAMILY_C should be filled
                    if($fields['GIVEN_C'] == '' || $fields['FAMILY_C'] == ''){
                        echo "ANOMALY ON NAMES - {$new['NUM']} - LINE SKIPPED, MUST BE FIXED\n";
                        continue;
                    }
                    else{
                        $family = $fields['FAMILY_C'];
                        $given = $fields['GIVEN_C'];
                    }
                }
                else{
                    [$family, $given] = explode(' ', $fields['NAME']); // ex 'Bally Etienne'
                    // If GIVEN_C or FAMILY_C are filled, override cura value
                    if($fields['FAMILY_C'] != ''){
                        $family = $fields['FAMILY_C'];
                    }
                    if($fields['GIVEN_C'] != ''){
                        $given = $fields['GIVEN_C'];
                    }
                }
                $new['FAMILYNAME'] = $family;
                $new['GIVENNAME'] = $given;
                // profession
                if($fields['PRO_C'] != ''){
                    $new['OCCU'] = $fields['OCCU_C'];
                }
                else{
                    $new['OCCU'] = $fields['OCCU'];
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
                    $admin2 = $fields['COD'];
                }
                if($admin2 == 'NONE'){
                    $admin2 = '';
                }
                if(strlen($admin2) == 1 && $country == 'FR'){
                    $admin2 = '0' . $admin2; // because libreoffice "eats" the trailing 0
                }
                // HERE try to match Geonames
                $slug = slugify::compute($place);
                $geonames = \Geonames::matchFromSlug([
                    'slug' => $slug,
                    'countries' => [$country],
                    'admin2-code' => $admin2,
                ]);
                if(!$geonames){
                    echo "ERROR: COULD NOT MATCH GEONAMES - {$new['NUM']} - $slug $admin2 - LINE SKIPPED, MUST BE FIXED\n";
                    continue;
                }
                $new['BIRTHPLACE']  = $geonames[0]['name'];
                $new['GEOID']       = $geonames[0]['geoid'];
                $new['LG']          = $geonames[0]['longitude'];
                $new['LAT']         = $geonames[0]['latitude'];
                $new['ADM2']        = $admin2;
                $new['COU']         = $country;
                //
                // birth date
                //
                try{
                    [$day, $hour] = self::computeBirthDate($fields['DATE'], $fields['DAY_C'], $fields['HOUR_C']);
                }
                catch(\Exception $e){
                    echo "ERROR: COULD NOT COMPUTE BIRTHDATE - {$new['NUM']} - $slug - LINE SKIPPED, MUST BE FIXED\n";
                    echo $e->getMessage() . "\n";
                    continue;
                }
                // dtu
                $dtu = '';
                if($country == 'FR'){
                    [$dtu, $err] = offset_fr::compute("$day $hour", $new['LG'], $new['ADM2']);
                    if($err != ''){
                        // err is something like :
                        // Possible timezone offset error (dept 54) - check precise local conditions
                        echo "ERROR for {$new['NUM']} {$new['FAMILYNAME']} {$new['GIVENNAME']} : " . $err . " - LINE SKIPPED, MUST BE FIXED\n";
                        continue;
                    }
                }
                else{
                    $dtu = offset::compute("$day $hour", $geonames[0]['timezone']);
                }
                $new['BIRTHDATE'] = "$day $hour$dtu";
                // add new line to res
                $res .= implode(G5::CSV_SEP, $new) . "\n";
            }
            // write output
            $dest_file = $dest_dir . DS . $groupCode . '.csv';
            file_put_contents($dest_file, $res);
            echo "$dest_file generated\n";
        }
    }
    
    
    // ******************************************************
    /**
        Auxiliary of self::generate()
        @param $date_cura   content of column DATE
        @param $day_c       content of column DAY_C
        @param $hour_c      content of column HOUR_C
        @return Array containing day and hour in format [YYYY-MM-DD, HH:MM:SS]
        @throws Exception
                    - in case of incoherence between cura and corrected data
                    - in case of incomplete data
    **/
    private static function computeBirthDate($date_cura, $day_c, $hour_c){
        if($day_c == '' && $hour_c == ''){
            // no checks on $date_cura, supposed correct
            $day = substr($date_cura, 0, 10);
            $hour = substr($date_cura, 11, 8);
            return [$day, $hour];
        }
        if($date_cura == ''){
            // happens for some rows, present in Gauquelin book, and not in cura data (ex Jacques Lunnis)
            if($day_c == ''){
                throw new \Exception("Missing column DAY_C");
            }                                                                                                         
            if($hour_c == ''){
                throw new \Exception("Missing column HOUR_C");
            }
        }
        if(is_numeric($hour_c)){
            // ex : convert '14' to '14:00:00'
            // obliged to do that because of libre office automatic conversion
            $hour_c = str_pad($hour_c, 2, '0', STR_PAD_LEFT) . ':00:00';
        }
        //
        $day = substr($date_cura, 0, 10);
        $hour = substr($date_cura, 11, 8);
        // override with corrected values
        if($day_c != ''){
            $day = $day_c;
        }
        if($hour_c != ''){
            $hour = $hour_c;
        }
        return [$day, $hour];
    }
    
    
}// end class
