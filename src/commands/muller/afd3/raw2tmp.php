<?php
/********************************************************************************
    Import data/raw/muller/afd3-women/muller-afd3-women.txt
    to data/tmp/muller/afd3-women/muller-afd3-women.csv
    
    @license    GPL
    @history    2021-04-11, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
//use tiglib\arrays\sortByKey;

class raw2tmp implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $report =  "--- muller afd3 raw2tmp ---\n";
        
        $raw = AFD3::loadRawFile();
        $res = implode(G5::CSV_SEP, AFD3::TMP_FIELDS) . "\n";
        $nLimits = count(AFD3::RAW_LIMITS);
        $N = 0;
        $day = $hour = '';
        foreach($raw as $line){
            $N++;
            $new = array_fill_keys(AFD3::TMP_FIELDS, '');
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = AFD3::RAW_FIELDS[$i];
                $offset = AFD3::RAW_LIMITS[$i];
                $length   = AFD3::RAW_LIMITS[$i+1] - AFD3::RAW_LIMITS[$i];
                $field = trim(substr($line, $offset, $length));
                switch($rawFieldname){
                case 'NAME':
                    [$new['FNAME'], $new['GNAME'], $new['ONAME']] = self::computeName($field);
                break;
                case 'DATE':
                    $day = self::computeDay($field);
                break;
                case 'TIME':
                    $hour = self::computeHour($field);
                break;
                case 'LAT':
                    $new['LAT'] = self::computeLat($field);
                break;
                case 'LG':
                    $new['LG'] = self::computeLg($field);
                break;
                default:
                    $new[$rawFieldname] = $field;
                break;
                }
            }
            $new['DATE'] = "$day $hour";
//echo "\n<pre>"; print_r($new); echo "</pre>\n"; exit;
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        $outfile = AFD3::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        return $report;
    }
    
    
    private static function computeLat($str) {
        $tmp = explode(' N ', $str);
        return round($tmp[0] + $tmp[1] / 60, 2);
    }
    
    private static function computeLg($str) {
        $tmp = explode(' ', $str);
        $res = $tmp[0] + $tmp[2] / 60;
        $res = $tmp[1] == 'W' ? -$res : $res;
        return round($res, 2);
    }
    
    private static function computeHour($hour) {
        return str_replace('.', ':', $hour);
    }
    
    private static function computeDay($str) {
        $tmp = explode('.', $str);
        if(count($tmp) != 3){
            echo "ERROR DAY $str\n";
            return $str;
        }
        return implode('-', [$tmp[2], $tmp[1], $tmp[0]]);
    }
    
    /**
        @return [$new['FNAME'], $new['GNAME'], $new['ONAME']]
    **/
    private static function computeName($str) {
        $pos = strpos($str, '(');
        if($pos !== false){
            $alt = substr($str, $pos+1, -1);
            $str = substr($str, 0, $pos);
        }
        $tmp = array_map('trim', explode(',', $str));
        if(count($tmp) == 3){
            if($tmp[2] == 'VON'){
                $fname = $tmp[2] . ' ' . $tmp[0];
                $gname = $tmp[1];
                return [$fname, $gname, ''];
            }
            return $tmp;
        }
        if(count($tmp) != 2){
            echo "================ ERROR NAME ================ $str\n";
            return [$str, '', ''];
        }
        $tmp[] = '';
        return $tmp;
    }
    
}// end class    

