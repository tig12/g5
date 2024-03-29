<?php
/********************************************************************************
    Import data/raw/muller/2-men/muller2-612-men.txt
    to data/tmp/muller/2-men/muller2-612-men.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-09-05 05:09:35+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m2men;

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
        
        $report =  "--- muller m2men raw2tmp ---\n";
        
        $raw = M2men::loadRawFile();
        $res = implode(G5::CSV_SEP, M2men::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, M2men::RAW_FIELDS) . "\n";
        
        $N = 0;
        $day = $hour = '';
        $nLimits = count(M2men::RAW_LIMITS);
        foreach($raw as $line){
            if(trim($line) == ''){
                continue;
            }
            $N++;
            $new = array_fill_keys(M2men::TMP_FIELDS, '');
            $new_raw = array_fill_keys(M2men::RAW_FIELDS, '');
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = M2men::RAW_FIELDS[$i];
                $offset = M2men::RAW_LIMITS[$i];
                $length   = M2men::RAW_LIMITS[$i+1] - M2men::RAW_LIMITS[$i];
                $field = trim(mb_substr($line, $offset, $length));
                $new_raw[$rawFieldname] = $field;
                switch($rawFieldname){
                case 'NAME':
                    [$new['FNAME'], $new['GNAME'], $new['FAME'], $new['NOBL']] = self::computeName($field);
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
                    $new['CY'] = M2men::COUNTRIES[$field];
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
        
        $outfile = M2men::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        
        $outfile = M2men::tmpRawFilename();
        file_put_contents($outfile, $res_raw);
        $report .= "Stored $N records in $outfile\n";
        
        return $report;
    }
    
    /**
        @return [$new['FNAME'], $new['GNAME'], $new['FAME'], $new['NOBL']]
    **/
    private static function computeName($str): array {
        // delete content between parentheses
        // concerns 2 records, managed by tweak files
        $str1 = trim(preg_replace('/(.*?)\s*\((.*?)\)\s*(.*?)/', '$1 $3', $str));
        // concerns 6 records
        $str1 = str_replace('I.', 'I', $str1);
        // concerns 1 record
        $str1 = str_replace('V.', 'V', $str1);
        //
        $tmp = explode(',', $str1);
        if(count($tmp) == 1){
            // only one component in the name => fame name
            return ['', '', $tmp[0], ''];
        }
        // here count($tmp) = 2
        $fname = $tmp[0];
        $gname1 = trim($tmp[1]);
        if(str_ends_with($gname1, ' de')){
            $gname = substr($gname1, 0, -3);
            $nobl = 'de';
        }
        else if(str_ends_with($gname1, ' di')){
            $gname = substr($gname1, 0, -3);
            $nobl = 'di';
        }
        else if(str_ends_with($gname1, " d'")){
            $gname = substr($gname1, 0, -3);
            $nobl = "d'";
        }
        else if(str_ends_with($gname1, ' von')){
            $gname = substr($gname1, 0, -4);
            $nobl = 'von';
        }
        else{
            $gname = $gname1;
            $nobl = '';
        }
        return [$fname, $gname, '', $nobl];
    }
    
    
    /**
        @return [$new['C1'], $new['C2'], $new['PLACE']]
    **/
    private static function computePlace($str, $cy): array {
        // content between parentheses => existence of C1 or C2
        preg_match('/.*?\((.*?)\)/', $str, $m);
        if(count($m) == 0){
            return ['', '', $str];
        }
        if(count($m) != 2){
            echo "================ ERROR PLACE ================ $str\n";
            return ['', '', $str];
        }
        $test = $m[1];
        $place = trim(str_replace("($test)" , '', $str));
// HERE must be completed
        $c1 = M2men::C1[$test] ?? '';
        $c2 = M2men::C2[$test] ?? '';
        return [$c1, $c2, $place];
    }
    
}// end class    

