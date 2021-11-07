<?php
/********************************************************************************
    Import data/raw/muller/3-women/muller3-234-women.txt
    to data/tmp/muller/3-women/muller3-234-women.csv
    
    @license    GPL
    @history    2021-04-11, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3women;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\muller\AFD;

class raw2tmp implements Command {
    
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $report =  "--- muller afd3 raw2tmp ---\n";
        
        $raw = AFD3women::loadRawFile();
        $res = implode(G5::CSV_SEP, AFD3women::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, AFD3women::RAW_FIELDS) . "\n";
        
        $nLimits = count(AFD3women::RAW_LIMITS);
        $N = 0;
        $day = $hour = '';
        foreach($raw as $line){
            $N++;
            $new = array_fill_keys(AFD3women::TMP_FIELDS, '');
            $new_raw = array_fill_keys(AFD3women::RAW_FIELDS, '');
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = AFD3women::RAW_FIELDS[$i];
                $offset = AFD3women::RAW_LIMITS[$i];
                $length   = AFD3women::RAW_LIMITS[$i+1] - AFD3women::RAW_LIMITS[$i];
                $field = trim(mb_substr($line, $offset, $length));
                $new_raw[$rawFieldname] = $field;
                switch($rawFieldname){
                case 'NAME':
                    [$new['FNAME'], $new['GNAME']] = self::computeName($field);
                break;
                case 'DATE':
                    $day = AFD::computeDay($field);
                break;
                case 'TIME':
                    $hour = AFD::computeHour($field);
                break;
                case 'TZO':
                    $new['TZO'] = AFD::computeTimezoneOffset($field);
                break;
                case 'PLACE':
                    // by chance, CY appears before place in raw file => can be passed here
                    [$new['C1'], $new['C2'], $new['PLACE']] = self::computePlace($field, $new['CY']);
                break;
                case 'LAT':
                    $new['LAT'] = AFD::computeLat($field);
                break;
                case 'CY':
                    $new['CY'] = AFD3women::COUNTRIES[$field];
                break;
                case 'LG':
                    $new['LG'] = AFD::computeLg($field);
                break;
                // other fields are simply copied
                default:
                    $new[$rawFieldname] = $field;
                break;
                }
            }
            $new['DATE'] = "$day $hour";
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $new_raw) . "\n";
        }
        
        $outfile = AFD3women::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        
        $outfile = AFD3women::tmpRawFilename();
        file_put_contents($outfile, $res_raw);
        $report .= "Stored $N records in $outfile\n";
        
        return $report;
    }
    
    /**
        Parts between parentheses and after the * are not exploited (too cahotic).
        @return [$new['FNAME'], $new['GNAME']]
    **/
    private static function computeName($str): array {
        // delete content between parentheses
        $str1 = trim(preg_replace('/(.*?)\s*\((.*?)\)\s*(.*?)/', '$1 $3', $str));
        // delete content after *
        $tmp = explode('*', $str1);
        if(count($tmp) == 2){
            $str2 = trim($tmp[0]);
        }
        else{
            $str2 = $str1;
        }
        // for names with 2 comas ($tmp has 3 elements), the last part is not used.
        $tmp = explode(',', $str2);
        $fname = ucwords(strtolower(trim($tmp[0])), '- ');
        if(count($tmp) < 2){
            // case of 177 RACHILDE *EYMERY, Marguerite Vallette - handled in tweak file
            $gname = '';
        }
        else{
            $gname = trim($tmp[1]);
        }
        return [$fname, $gname];
    }
    
    private static function computePlace($str, $cy): array {
        // content between parentheses => existence of C1 or C2
        $c1 = $c2 = '';
        $place = $str;
        preg_match('/.*?\((.*?)\)/', $str, $m);
        if(count($m) == 0){
            return [$c1, $c2, $place];
        }
        if(count($m) != 2){
            echo "================ ERROR PLACE ================ $str\n";
            return [$c1, $c2, $place];
        }
        $test = $m[1];
        if($test == 'Dresden'){
            // particular case, indicates a nearby city, not admin division
            return ['13', $c2, $place];
        }
        if(in_array($cy, ['DE', 'AT'])){
            // indications in parentheses are confused, sometimes refer to c1, sometimes to c2
            // sometimes to city => didn't try exhaustive match.
            return [$c1, $c2, $place];
        }
        $place = trim(str_replace("($test)" , '', $str));
        $c1 = AFD3women::C1[$test] ?? '';
        $c2 = AFD3women::C2[$test] ?? '';
        return [$c1, $c2, $place];
    }
    
} // end class    

