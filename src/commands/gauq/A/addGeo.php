<?php
/********************************************************************************
    Adds field GEOID and corrects field PLACE to files of data/tmp/gauq/lerrcp/A*.csv
    
    TODO Could add an entry in 'todo' field of persons without geoid (but useless)
    
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-07-03 06:48:00+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\Geonames;
use g5\commands\gauq\LERRCP;
use tiglib\strings\slugify;
use tiglib\geonames\database\matchFromSlug;


class addGeo implements Command {
    
    private static $pdo_geonames;

    const POSSIBLE_PARAMS = [
        'full' => "lists all the place names that can't be matched to geonames.",
        'medium' => 'lists the places with several matches to geonames.',
        'small' => 'only echoes global information about matching.',
    ];
    
    /** 
        Called by : php run-g5.php gauq A1 addGeo
        @param $params array that must contain 3 elements :
                       - datafile : string like 'A1'
                       - command : useless
                       - report type : "full" "medium" or "small"
    **/
    public static function execute($params=[]): string{
        
        $msg = "Possible values for parameter:\n";
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $msg .= "  '$k' : $v\n";
        }

        if(count($params) < 3){
            return "WRONG USAGE - This command needs a parameter indicating the type of report\n$msg";
        }
        if(count($params) > 3){
            return "WRONG USAGE - Useless parameter \"{$params[3]}\".\n";
        }
        
        if(!in_array($params[2], array_keys(self::POSSIBLE_PARAMS))){
            return "WRONG USAGE - Invalid value : {$params[2]}. Possible values :\n$msg";
        }
        
        $datafile = $params[0];
        $report_type = $params[2];
        
        self::$pdo_geonames = Geonames::compute_dblink();
        
        $report = "--- gauq $datafile addGeo ---\n";
        $rows = LERRCP::loadTmpFile($datafile);
        $res = implode(G5::CSV_SEP, A::TMP_FIELDS) . "\n";
        
        $N = $ok = 0;
        
        foreach($rows as $row){
            $N++;
            $new = $row;
            // if($row['CY'] != 'FR'){
                // $res .= implode(G5::CSV_SEP, $new) . "\n";
                // continue;
            // }
            if($row['CY'] == 'FR'){
                $new['PLACE'] = self::correct_place_fr($new['PLACE']);
            }
            
            $slug = slugify::compute($new['PLACE']);
            $geonames = matchFromSlug::compute(self::$pdo_geonames, [
                'slug' => $slug,
                'countries' => [$row['CY']],
                'admin2-code' => $new['C2'],
            ]);
            if($geonames){
                if(count($geonames) > 1 && $report_type != 'small'){
                    $report .= "WARNING : several possible geonames matches for row :\n"
                             . "    " . $new['NUM'] . ' ' . $new['GNAME'] . ' ' . $new['FNAME'] . ' -- ' . $new['PLACE'] . "\n";
                    for($i=0; $i < count($geonames); $i++){
                        $report .= "    " . $geonames[$i]['geoid'] . ' ' . $geonames[$i]['name'] . ' '
                                . $geonames[$i]['longitude'] . ' ' . $geonames[$i]['latitude'] . "\n";
                    }
                }
                else{
                    $ok++;
                    $new['PLACE'] = $geonames[0]['name'];
                    $new['GEOID'] = $geonames[0]['geoid'];
                    $new['LG'] = $geonames[0]['longitude'];
                    $new['LAT'] = $geonames[0]['latitude'];                                          
                }
            }
            if(!$geonames && $report_type == 'full'){
                $report .= $new['NUM'] . ' ' . $new['GNAME'] . ' ' . $new['FNAME'] . ' -- ' . $new['PLACE'] . "\n";
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        $p = round($ok / $N * 100, 2);
        $miss = $N - $ok;
        if($report_type != 'small'){
            if($miss != 0){
                $report = "Records that didn't match geonames :\n" . $report;
            }
            $report .= "-------\n";
        }
        $report .= "$datafile : geonames ok = $ok ($p %) - miss $miss\n";
        
        $csvfile = LERRCP::tmpFilename($datafile);
        file_put_contents($csvfile, $res);
        
        return $report;
    }
    
    
    // ******************************************************
    /** 
        Corrects French place names
        Auxiliary of execute().
        Not handled :
            Mouthiers-Hte-P
        @return Corrected place name
    **/
    private static function correct_place_fr(string $str): string {
        $place;
        
        if(strtoupper($str) != $str){
            // cura5 files contain only uppercased place names
            // => place name already corrected (by tweak2tmp step)
            return $str;
        }
        
        $str = ucWords(strtolower($str), " -'/\t\r\n\f\v"); // delim = default + "-", "'" and "/"
                      
        $lowers = ['Le', 'La', 'Les', 'Du', 'De', 'Des', 'Sur', 'En'];
        
        $parts = explode(' ', $str);
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
            $place = implode(' ', $parts2);
        }
        else{
            $place = $str;
        }
        
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
        // TODO FIX - for ex the character following "/" should be uppercased
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
        return $place;
    }
    
} // end class    
