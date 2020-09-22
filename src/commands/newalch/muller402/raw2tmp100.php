<?php
/********************************************************************************
    
    Converts data/raw/newalchemypress.com/05-muller-writers/muller-afd1-100-writers.txt
    to data/tmp/newalch/05-muller-writers/muller-afd1-100-writers.csv
    
    @license    GPL
    @history    2020-08-01 02:49:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class raw2tmp100 implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty Array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        $p = '/'
            . '(?P<id>\d+)'
            . '(?P<sex>[MF])'
            . '\s+(?P<fname>.*?)'
            . ',\s*(?P<gname>.*?)'
            . '\s+(?P<date>\d{2}\.\d{2}\.\d{4})'
            . '\s+(?P<tz>(?:\-?\d{1}\.\d{2}|LMT))'
            . '\s+(?P<place>.*?)'
            . '\s+(?P<c2>[A-Z]{2})'
            . '\s+(?P<lat>\d+\s+N\s+\d+)'
            . '\s+(?P<lg>(?:\d{3} |\d \d )E\s+\d+)'
            . '\s+(?P<OCCU>\d+)'
            . '\s+(?P<OPUS>\d+)'
            . '\s+(?P<LEN>\d+)'
            . '/';
        
        // TODO put in Muller402 constants
        $infile = Muller100::rawFilename();
        $lines = file($infile);
        
        $csvFields = Muller100::TMP_FIELDS;
        
        $emptyNew = array_fill_keys(Muller100::TMP_FIELDS, '');
        $res = implode(G5::CSV_SEP, Muller100::TMP_FIELDS) . "\n";
        // to keep trace of original raw fields
        $emptyNewRaw = array_fill_keys(Muller100::RAW_FIELDS, '');
        $resRaw = implode(G5::CSV_SEP, Muller100::RAW_FIELDS) . "\n";
        
        $N = 0;
        foreach($lines as $line){
            if(trim($line) == ''){
                continue;
            }
            preg_match($p, $line, $m);
            if(count($m) == 0){                                   
                echo "LINE NOT MATCHING\n";
                die($line);
            }
            $new = $emptyNew;
            $newRaw = $emptyNewRaw;
            $new['MUID'] = $newRaw['MUID'] = $m['id'];
            $new['FNAME'] = $newRaw['FNAME'] = $m['fname'];
            $new['GNAME'] = $newRaw['GNAME'] = $m['gname'];
            $new['SEX'] = $newRaw['SEX'] = $m['sex'];
            $new['DATE'] = $newRaw['DATE'] = substr($m['date'], 6) . '-' . substr($m['date'], 3, 2) . '-' . substr($m['date'], 0, 2);
            $newRaw['TZO'] = $m['tz'];
            // $new['TZO'] must be computed after longitude
            $new['LMT'] = ($m['tz'] == 'LMT' ? 'LMT' : '');
            $new['PLACE'] = $newRaw['PLACE'] = $m['place'];
            $new['C2'] = $newRaw['C2'] = $m['c2'];
            $new['CY'] = 'IT';
            $new['LG'] = self::compute_lg($m['lg']);
            $new['TZO'] = Muller402::compute_offset($m['tz'], $new['LG']);
            $newRaw['LG'] = $m['lg'];                                                                                    
            $new['LAT'] = self::compute_lat($m['lat']);
            $newRaw['LAT'] = $m['lat'];
            $new['OCCU'] = $newRaw['OCCU'] = $m['OCCU'];
            $new['OPUS'] = $newRaw['OPUS'] = $m['OPUS'];
            $new['LEN'] = $newRaw['LEN'] = $m['LEN'];
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $resRaw .= implode(G5::CSV_SEP, $newRaw) . "\n";
            $N++;
        }
        $report =  "--- muller100 raw2tmp ---\n";
        $outfile = Muller100::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Wrote $N lines in $outfile \n";
        //
        $outfile = Muller100::tmpRawFilename();
        file_put_contents($outfile, $resRaw);
        $report .= "Stored $N records in $outfile\n";
        return $report;
    }
    
    /** 
        @param $str String like "012 E 15" (all strings are east)
    **/
    private static function compute_lg($str){
        $tmp = explode('E', $str);
        return (int)trim($tmp[0]) + trim($tmp[1]) / 60;
    }
    
    /** 
        @param $str String like "44 N 24" (all strings are north)
    **/
    private static function compute_lat($str){
        $tmp = explode('N', $str);
        return (int)trim($tmp[0]) + trim($tmp[1]) / 60;
    }
    
}// end class    

