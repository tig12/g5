<?php
/********************************************************************************
    Builds a list of XXX mathematicians ranked by eminence.
    Data source : book
        Une histoire des mathématiques
        Routes et dédales
        Amy Dahan-Dalmedico
        Jeanne Peiffer
        Editions du Seuil, 1986
    Input
        data/auxiliary/maths/peiffer-dahan-dalmenico/peiffer-dahan-dalmenico.txt
    Output
        data/build/eminence/math/pdd.yml
    
    @license    GPL
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
    @history    2020-08-17 23:36:30+02:00, Thierry Graff : Conert from raw2full to raw2tmp
********************************************************************************/
namespace g5\commands\eminence\math;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
//use tiglib\arrays\sortByKey;

class 


class pdd implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
die("\n<br>die here " . __FILE__ . ' - line ' . __LINE__ . "\n");
        $report =  "--- muller402 raw2tmp ---\n";
        
        $pname = '/(\d+)([MFK])\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $emptyNew = array_fill_keys(Muller402::TMP_FIELDS, '');
        $res = implode(G5::CSV_SEP, Muller402::TMP_FIELDS) . "\n";
        $resRaw = implode(G5::CSV_SEP, Muller402::RAW_FIELDS) . "\n"; // keep trace of original raw fields
        $N = 0;
        $raw = Muller402::loadRawFile();
        foreach($raw as $line){
            $fields = explode(Muller402::RAW_SEP, $line);
            $new = $emptyNew;
            $new['OCCU'] = 'WR'; /////// HERE TODO put wikidata occupation id ///////////
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
                // @todo should be verified by human and included in tweaks
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
            // These are handled by Muller100
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
            $new['TZO'] = Muller402::compute_offset($fields[6], $new['LG']);
            if($fields[6] == 'LMT'){
                $new['LMT'] = 'LMT';
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $resRaw .= implode(G5::CSV_SEP, $fields) . "\n";
            $N++;
        }
        
        $outfile = Muller402::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        //
        $outfile = Muller402::tmpRawFilename();
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
    
}// end class    

