<?php
/********************************************************************************
    Converts raw Gauquelin 1955 files in from data/raw/gauq/g55/
    to temporary csv files in data/tmp/gauq/g55/

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-25 23:22:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
// use g5\commands\gauq\LERRCP;

class raw2tmp implements Command {
    
    // ******************************************************
    /** 
        Parses one file E1 or E3 and stores it in a csv file
        The resulting csv file contains informations of the 2 lists
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here)
                        - the string "raw2tmp" (useless here)
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
        @return report
    **/
    public static function execute($params=[]): string{
        
        $cmdSignature = 'gauq g55 raw2tmp';
        $report = "--- $cmdSignature ---\n";
        
        $tmp = G55::GROUPS;
        unset($tmp['884PRE']);
        $possibleParams = array_keys($tmp);
        $msg = "Usage : php run-g5.php $cmdSignature <group>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        
        if(count($params) != 3){
            return "INVALID CALL: - this command needs exactly one parameter.\n$msg";
        }
        $groupKey = $params[2];
        if(!in_array($groupKey, $possibleParams)){
            return "INVALID PARAMETER: $groupKey\n$msg";
        }
        if(!isset(G55::GROUPS[$groupKey]['raw-file'])){
            return "INVALID GROUP: Group $groupKey does not have raw file.\n$msg";
        }
        
        $outfile = G5::tmpFilename($datafile);
        $outfile_raw = G5::tmpRawFilename($datafile);
        
        $N = 0;
        $raw = G55::loadRawFile($groupKey);
        $res = implode(G5::CSV_SEP, G55::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, G55::RAW_FIELDS) . "\n";
        $newEmpty = array_fill_keys(G55::TMP_FIELDS, '');
        $trimField = function(&$val, $idx){ $val = trim($val); };
        foreach($raw as $line){
            $N++;
            $fields = explode(G55::RAW_SEP, trim($line));
            if(count($fields) != 4){
                throw new \Exception("Incorrect format in file $groupKey for line $N:\n$line");
            }
            array_walk($fields, $trimField);
            $new = $newEmpty;
            $new['NUM'] = $N;
            [$new['FNAME'], $new['GNAME']] = self::computeName($fields[0]);
            $new['DATE'] = self::computeDateTime($fields[1], $fields[2]);
            [$new['PLACE'], $new['C2'], $new['CY']] = self::computePlace($fields[3]);
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $fields) . "\n";
        }
        
        
exit;
        
        file_put_contents($outfile, $csv);
        $report .= "Stored " . self::$n_total . " lines in $outfile\n";
        
        file_put_contents($outfile, $csvRaw);
        $report .= "Stored " . self::$N . " lines in $outfile\n";
        
        return $report;
    }
    
    
    
    // ******************************************************
    const PATTERN_NAME = '/([A-Z ]+) (.*)/';
    /**
        @return     Array [family name, given name]
    **/
    private static function computeName($str) {
        $res = ['', ''];
        preg_match(self::PATTERN_NAME, $str, $m);
        if(count($m) != 3){
            //throw new \Exception("Unable to parse G55 name: $str");
            print("Unable to parse G55 name: $str\n");
        }
        $res[0] = ucWords(strtolower($m[1]));
        $res[1] = $m[2];
        return $res;
    }
    
    // ******************************************************
    /**
        @return     Date formt YYYY-MM-DD HH:MM
    **/
    private static function computeDateTime($strDay, $strHour) {
        $res = '';
        return $res;
    }
    
    // ******************************************************
    /**
        @return     Array [place name, admin code level 2, country iso 3166]
    **/
    private static function computePlace($str) {
        $res = ['', '', ''];
        return $res;
    }
    
    
}// end class    
