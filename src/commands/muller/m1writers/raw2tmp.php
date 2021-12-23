<?php
/********************************************************************************
    Import data/raw/muller/1-writers/5muller_writers.csv
    to data/data/tmp/muller/1-writers/muller1-402-writers.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
    @history    2020-08-17 23:36:30+02:00, Thierry Graff : Conert from raw2full to raw2tmp
********************************************************************************/
namespace g5\commands\muller\m1writers;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\Newalch;
use tiglib\arrays\sortByKey;

class raw2tmp implements Command {
    
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        $report =  "--- muller m1writers raw2tmp ---\n";
        
        $pname = '/(\d+)([MFK])\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $emptyNew = array_fill_keys(M1writers::TMP_FIELDS, '');
        $res = implode(G5::CSV_SEP, M1writers::TMP_FIELDS) . "\n";
        $resRaw = implode(G5::CSV_SEP, M1writers::RAW_FIELDS) . "\n"; // keep trace of original raw fields
        $N = 0;
        $raw = M1writers::loadRawFile();
        foreach($raw as $line){
            $fields = explode(M1writers::RAW_SEP, $line);
            $new = $emptyNew;
            // raw file 5muller_writers.csv doesn't contain the occupation (between 1 to 5)
            // but Müller booklet contains it => a new OCR of the original would permit to have a more precise occupation
            $new['OCCU'] = 'writer';
            preg_match($pname, $fields[0], $m);
            $sex = $m[2];
            if($sex != 'M' && $sex != 'F'){
                // happens only for 478K Villaruel, Giuseppe
                // Comparision with scan of original Müller's AFD shows it's an OCR error
                $sex='M';
            }
            $new['SEX'] = $sex;
            $mullerId = $m[1];
            $new['MUID'] = $mullerId;
            
            $nameFields = explode(',', $m[3]);
            if(count($nameFields) == 2){
                // normal case
                $new['FNAME'] = $nameFields[0];
                $new['GNAME'] = trim($nameFields[1]);
            }
            else{
                // empty given names
                // TODO should be verified by human and included in tweaks
                if($mullerId == '310' || $mullerId == '387'){
                    $new['FNAME'] = $nameFields[0];
                    $new['GNAME'] = '';
                }
            }
            if($mullerId == '23'){
                $new['GNAME'] = 'Ambrogio'; // OCR error
            }
            
            $new['DATE'] = $fields[1].'-'.$fields[2].'-'.$fields[3];
            if($fields[4] != '' && $fields[5] != ''){
                $new['DATE'] .= ' '.$fields[4].':'.$fields[5];
            }
            
            //
            // keep only records with complete birth time (at least YYYY-MM-DD HH:MM)
            // These are handled by M1writers100
            //
            if(strlen($new['DATE']) < 16){
                continue;
            }
            
            preg_match($pplace, $fields[7], $m);
            $new['PLACE'] = $m[1];
            $new['C2'] = $m[2];
            // Fix C2
            if($new['PLACE'] == 'Verona'){
                // systematic error in M402 file
                $new['C2'] = 'VR';
            }
            if($mullerId == '76'){
                $new['C2'] = 'ME'; // OCR error
            }
            if($mullerId == '369'){
                $new['C2'] = 'CH'; // OCR error
            }
            $new['CY'] = 'IT';
            $new['LG'] = self::lglat(-(int)$fields[9]); // minus sign, correction from raw here
            $new['LAT'] = self::lglat($fields[8]);
            $new['TZO'] = M1writers::compute_offset($fields[6], $new['LG']);
            if($fields[6] == 'LMT'){
                $new['LMT'] = 'LMT';
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $resRaw .= implode(G5::CSV_SEP, $fields) . "\n";
            $N++;
        }
        
        $outfile = M1writers::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        //
        $outfile = M1writers::tmpRawFilename();
        file_put_contents($outfile, $resRaw);
        $report .= "Stored $N records in $outfile\n";
        return $report;
    }
    
    /** 
        The string written in 5muller_writers.csv is already converted to decimal degrees
        Different from original booklet
    **/
    private static function lglat(string $str): string {
        return str_replace(',', '.', $str);
    }
    
    /**
        Conversion of TZ offset found in newalch file to standard sHH:MM offset.
        WARNING : possible mistake for "-0.6" :
            0.6*60 = 36
            "Problèmes de l'heure résolus pour le monde entier",
            Françoise Schneider-Gauquelin (p 288) indicates 00:37
            Current implementation uses Gauquelin, but needs to be confirmed
        @param $offset  timezone offset as specified in newalch file
        @param $lg      longitude, as previously computed
    **/
    public static function compute_offset($offset, $lg){
        if($offset == 'LMT'){ 
            // happens for 5 records
            // convert longitude to HH:MM:SS
            $sec = $lg * 240; // 240 = 24 * 3600 / 360
            return '+' . seconds2HHMMSS::compute($sec);
        }
        switch($offset){
        	case '-1': 
        	    return '+01:00';
        	break;
        	case '-0,83': 
        	    return '+00:50';
        	break;
        	case '-0,88': 
        	    // Converting geonames.org longitude for Palermo (13°20'08") gives 00:53:34
        	    // Gauquelin says 00:54
        	    // Gabriel says 00:53:28
        	    return '+00:54';
        	break;
        	case '-0,6': 
        	    return '+00:37';
        	break;
            default:
                throw new \Exception("Timezone offset not handled in M1writers : $offset");
        }
    }
    
} // end class
