<?php
/********************************************************************************
    Import data/raw/muller/4-dynasties/muller-1145-utf8.txt.zip
    to data/tmp/muller/4-dynasties/muller4-1145-dynasties.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-19 22:20:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m4dynasties;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
//use g5\commands\muller\AFD;
use g5\commands\muller\Muller;

class raw2tmp implements Command {
    
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $report =  "--- muller m4dynasties raw2tmp ---\n";
        
        $raw = M4dynasties::loadRawFile();
//echo "\n<pre>"; print_r(array_slice($raw, 0, 10)); echo "</pre>\n";
//        $res = implode(G5::CSV_SEP, M4dynasties::TMP_FIELDS) . "\n";
//        $res_raw = implode(G5::CSV_SEP, M4dynasties::RAW_FIELDS) . "\n";
        
        $N = 0;
        $day = $hour = '';
        $nLines = count($raw);
        for($i=0; $i < $nLines;){
            $N++;
            $MUID = Muller::mullerId('afd4', $N);
            $line1 = $raw[$i];
            $i++;
            $line2 = $raw[$i];
            $i++;
            $line3 = $raw[$i];
            $i++;
            $line4 = $raw[$i];
            $i++;
echo "$line1\n";
//echo "$line2\n";
//echo "$line3\n";
//echo "$line4\n";
//echo "$MUID\n";
if($N == 25) exit;
//            $new = array_fill_keys(M4dynasties::TMP_FIELDS, '');
//            $new_raw = array_fill_keys(M4dynasties::RAW_FIELDS, '');
        }
        
        $outfile = M4dynasties::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        
        $outfile = M4dynasties::tmpRawFilename();
        file_put_contents($outfile, $res_raw);
        $report .= "Stored $N records in $outfile\n";
        
        return $report;
    }
    
} // end class    

