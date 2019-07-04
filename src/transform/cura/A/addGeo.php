<?php
/********************************************************************************
    Adds field GEOID and corrects field PLACE to files of 5-cura-csv
    For some fields, adds information in field NOTES
    
    @license    GPL
    @history    2019-07-03 06:48:00+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\Cura;

class addGeo implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura A1 addGeo
        @param $params array that must contain 2 elements :
                       - datafile : string like 'A1'
                       - command : useless
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 2){
            return "WRONG USAGE - useless parameter : {$params[2]}\n";
        }
        
        $datafile = $params[0];
        
        $report = '';
        $rows = Cura::loadTmpCsv($datafile);
        $res = implode(G5::CSV_SEP, Cura::TMP_CSV_COLUMNS) . "\n";

        foreach($rows as $row){
            $new = $row;
            if($row['CY'] != 'FR'){
                $res .= implode(G5::CSV_SEP, $new) . "\n";
                continue;
            }
            [$new['PLACE'], $new['NOTE']] = self::correct_place($new['PLACE']);
/* 
[$newplace, $new['NOTE']] = self::correct_place($new['PLACE']);
if($newplace != $new['PLACE']){
    //echo $new['PLACE'] . "\t\t$newplace\t*** " . $new['NOTE'] . "\n";
    //echo $new['PLACE'] . "\t\t$newplace\n";
if(strpos($newplace, ' ') === false && strpos($newplace, ' ') === false) continue;
    echo $newplace . "\n";
}
*/ 

continue;
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        return $report;
    }
    
    
    // ******************************************************
    /** 
        Auxiliary of execute();
        Not handled :
            Mouthiers-Hte-P
        @return Array with 2 elements : place and notes.
    **/
    private static function correct_place($str){
//if($str != 'BOULOGNE S-MER') return [$str, ''];
        $place = $notes = '';
        
        if(strtoupper($str) != $str){
            // cura files contain only uppercased place names
            // => place name already corrected
            return [$str,$notes];
        }
        
        $str = ucWords(strtolower($str), " -'/\t\r\n\f\v"); // delim = default + "-"
        
        $parts = explode(' ', $str);
        
        // Paris
        // Potential bug for Paris-l'Hôpital - not present in cura files
        if($parts[0] == 'Paris'){
            if(count($parts) != 1){
                $notes = $str; // ex "Paris 14e"
            }
            return ['Paris', $notes];
        }
        // Lyon
        if($parts[0] == 'Lyon'){
            if(count($parts) != 1){
                $notes = $str; // ex "Lyon 14e"
            }
            return ['Lyon', $notes];
        }
        
        $lowers = ['Le', 'La', 'Les', 'Du', 'De', 'Des', 'Sur', 'En'];
        
        if(count($parts) != 1){
//echo "\n<pre>"; print_r($parts); echo "</pre>\n";
            $parts2 = [];
            for($i=0; $i < count($parts); $i++){
                if(in_array($parts[$i], $lowers)){
                    $parts2[] = strtolower($parts[$i]);
                }
                else if($parts[$i] == 'St'){
                    $parts2[] = 'Saint';
                }
                else if($parts[$i] == 'Ste'){
                    $parts2[] = 'Sainte';
                }
                else if($parts[$i] == 'S'){
                    $parts2[] = 'sur';
                }
                else if($parts[$i] == 'S/'){
                    $parts2[] = 'sur';
                }
                else{
                    $parts2[] = $parts[$i];
                }
            }
            $place = implode(' ', $parts2);
        }
        else{
            $place = $str;
        }
//echo "$place\n";
        
        $parts = explode('-', $place);
        if(count($parts) != 1){
            $parts2 = [];
            for($i=0; $i < count($parts); $i++){
                if(in_array($parts[$i], $lowers)){
                    $parts2[] = strtolower($parts[$i]);
                }
                else if($parts[$i] == 'St'){
                    $parts2[] = 'Saint';
                }
                else if($parts[$i] == 'Ste'){
                    $parts2[] = 'Sainte';
                }
                else if($parts[$i] == 'S'){
                    $parts2[] = 'sur';
                }
                else if($parts[$i] == 'S/'){
                    $parts2[] = 'sur';
                }
                else{
                    $parts2[] = $parts[$i];
                }
            }
            $place = implode('-', $parts2);
        }
        
        $place = ucfirst($place); // for places starting for ex by "La "
        // @todo FIX - for ex the character following "/" should be uppercased
        for($i=0; $i < 3; $i++){
            $place = strtr($place, [
                'S/' => 'sur-',
                ' Saint' => '-Saint',
                'Saint ' => 'Saint-',
                'Sainte ' => 'Sainte-',
                ' sur' => '-sur',
                ' S-' => '-sur-',
            ]);
            // Saint-Loup/Semouse
        }
        return [$place, $notes];
    }
    
}// end class    

