<?php
/********************************************************************************
    Importation of 1-si-raw/si42-p62-65.txt
    to  5-csicop/408-csicop-si42.csv (all records)
    and 5-csicop/181-csicop-si42.csv (records marked "SC")
    
    @license    GPL
    @history    2019-11-16, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\si42;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class raw2csv implements Command{
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = SI42::raw_filename();
        $outfile_181 = SI42::tmp_filename_181();                 
        $outfile = SI42::tmp_filename();
        
        $report =  "--- Importing file $infile ---\n";
        
        $res = $res_181 = implode(G5::CSV_SEP, SI42::TMP_FIELDS) . "\n";
        $lines = file($infile);
        $n = $n_181 = 0;
        foreach($lines as $line){
            $line = trim($line);
            if($line == ''){
                continue;
            }
            $n++;
            $new = [];
            $new['CSID'] = $n;
            [$new['FNAME'], $new['GNAME']] = self::parseName(substr($line, 0, 29));
            $new['DATE'] = self::parseDate(substr($line, 30, 10));
            $new['C2'] = substr($line, 40, 2);
            $new['MA12'] = trim(substr($line, 51, 2));
            $new['SC'] = trim(substr($line, 55));
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            if($new['SC'] != ''){
                $n_181++;
                $res_181 .= implode(G5::CSV_SEP, $new) . "\n";
            }
        }
        
        file_put_contents($outfile_181, $res_181);
        $report .= "Generated $outfile_181 - $n_181 records stored\n";
        file_put_contents($outfile, $res);
        $report .= "Generated $outfile - $n records stored\n";
        return $report;
    }
    
    
    // ******************************************************
    private static function parseDate($str){
        $str = trim($str);
        [$m, $d, $y] = explode('/', $str);
        $m = str_pad($m, 2, '0', STR_PAD_LEFT);
        $d = str_pad($d, 2, '0', STR_PAD_LEFT);
        $y = ($y > 60 ? '18' : '19') . $y;
        return "$y-$m-$d";
    }
    
    // ******************************************************
    private static function parseName($str){
        $fname = $gname = '';
        $tmp = explode(',', $str);
        if(count($tmp) != 2){
            die("LINE TO FIX :\n$str\n");
        }
        $fname = trim($tmp[0]);
        $gname = trim($tmp[1]);
        return [$fname, $gname];
    }
    
    
    
}// end class    

