<?php
/********************************************************************************
    Importation of data/raw/cfepp/final3
    to  data/tmp/cfepp/cfepp-1120-nienhuys.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-20 18:19:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class raw2tmp implements Command {
    
    /**
        Pattern for NAME in raw file.
        Ex of name: DUFRESNE Jean-Pierre
    **/
    const PNAME = "/([A-Z' -]+) (.*)/";
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Final3::rawFilename();
        $outfile = Final3::tmpFilename();
        $outfileRaw = Final3::tmpRawFilename();
        
        $report =  "--- cfepp final3 raw2tmp ---\n";
        
        $lines = Final3::loadRawFile();
        
        $N = 0;
        $res = implode(G5::CSV_SEP, Final3::TMP_FIELDS) . "\n";
        $resRaw = implode(G5::CSV_SEP, Final3::RAW_FIELDS) . "\n"; // to keep trace of the original values
        $nLimits = count(Final3::RAW_LIMITS);
        foreach($lines as $line){
            if($line == ''){ // last line
                continue;
            }
            $N++;
            $new = [];
            $new_raw = [];
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = Final3::RAW_FIELDS[$i];
                $offset = Final3::RAW_LIMITS[$i];
                $length   = Final3::RAW_LIMITS[$i+1] - Final3::RAW_LIMITS[$i];
                $field = trim(substr($line, $offset, $length));
                $new_raw[$rawFieldname] = $field;
            }
            $resRaw .= implode(G5::CSV_SEP, $new_raw) . "\n";
            $new['CFID']    = $N;
            $new['GQID']    = '';
            $new['ERID']    = '';
            $new['CPID']    = '';
            $new['OCCU']    = Final3::RAW_OCCUS[$new_raw['SPORT']];
            $new['SRC']     = $new_raw['SRC'];
            $new['LV']      = $new_raw['LV'];
            [ $new['FNAME'], $new['GNAME'] ] = self::computeName($new_raw['NAME']);
            $new['DATE']    = self::computeDate($new_raw['LOC_DATE'], $new_raw['LT']);
            $new['DATE-UT'] = self::computeDate($new_raw['UNIV_DATE'], $new_raw['UT']);
            $new['PLACE']   = $new_raw['BIRTH_PLACE'];
            [ $new['C2'], $new['C3'] ] = self::computePostal($new_raw['POSTAL_CODE']);
            $new['LG']      = str_replace('+', '', $new_raw['LONG']);
            $new['LAT']     = str_replace('+', '', $new_raw['LAT']);
            $new['M12']     = $new_raw['S'];
            $res .= implode(G5::CSV_SEP, $new) . "\n";
//if($N == 10) break;
        }
//echo "$res\n";
//exit;
        
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        
        // keep trace of the original values
        file_put_contents($outfileRaw, $resRaw);
        $report .= "Stored $N records in $outfileRaw\n";
        
        return $report;
    }
    
    private static function computeName($name){
        if($name == 'MARTIN-LEGEAY'){
            return [$name, '']; // particular case without given name
        }
        preg_match(self::PNAME, $name, $m);
        return [$m[1], $m[2]];
    }
    
    private static function computeDate($day, $time){
        return str_replace(' ', '-', $day) . ' ' . str_replace(' ', ':', $time);
    }
    
    private static function computePostal($postal){
        $c2 = substr($postal, 0, 2);
        $c3 = '';
        if($c2 == '75'){
            $candidate = (int)substr($postal, 2, 3);
            if($candidate >=1 && $candidate <= 20){
                $c3 = $candidate;                
            }
        }
        return [$c2, $c3];
    }
    
} // end class