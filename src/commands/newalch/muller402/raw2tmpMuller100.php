<?php
/********************************************************************************
    
    Imports in data/tmp/newalch/ the second Müller's list containing 100 persons without birth times.
    
    @license    GPL
    @history    2020-08-01 02:49:29+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\Config;
use g5\patterns\Command;

class raw2tmpMuller100 implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        Converts data/raw/newalchemypress.com/05-muller-writers/muller-100-it-writers.txt
        to data/raw/newalchemypress.com/05-muller-writers/muller-100-it-writers.csv
        @param  $params empty Array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        $p = '/'
            . '(?P<id>\d+)'
            . '(?P<sex>[MF])'
            . '\s+(?P<fname>.*?)'
            . ',(?P<gname>.*?)'
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
        $res = implode(G5::CSV_SEP, Muller100::TMP_FIELDS) . "\n";
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
            $cur = [];
            $cur[] = $m['id'];
            $cur[] = $m['fname'];
            $cur[] = $m['gname'];
            $cur[] = $m['sex'];
            $cur[] = substr($m['date'], 6) . '-' . substr($m['date'], 3, 2) . '-' . substr($m['date'], 0, 2);
            $cur[] = $m['tz'];
            $cur[] = $m['place'];
            $cur[] = $m['c2'];
            $cur[] = 'IT';
            $cur[] = $m['lg'];
            $cur[] = $m['lat'];
            $cur[] = $m['OCCU'];
            $cur[] = $m['OPUS'];
            $cur[] = $m['LEN'];
            $res .= implode(G5::CSV_SEP, $cur) . "\n";
            $N++;
        }
        $report =  "--- muller100 raw2tmp ---\n";
        $outfile = Muller100::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $res);
        $report .= "Wrote $N lines in $outfile \n";
        return $report;
    }
    
    
}// end class    

