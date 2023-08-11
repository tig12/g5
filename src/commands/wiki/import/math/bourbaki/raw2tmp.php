<?php
/********************************************************************************
    Builds a list of 458 mathematicians ranked by eminence.
    Data source : book
        Éléments d'histoire des Mathématiques
        by Nicolas Bourbaki
        Ed. Springer
        2007 (3rd edition ; reprint from 1984 version, ed. Masson)
        Uses the "INDEX DES NOMS CITÉS", pp 366 - 376 of the book
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-11-26 21:59:17+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\import\math\bourbaki;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;


// *****************************************
//          Implementation of Command
// *****************************************
class raw2tmp implements Command {
    
    /** 
        @param  $params
                    - $params[0] contains the name of the action (ex raw2tmp)
                    - Other params are passed to the exec_* method
        @return String report
    **/
    public static function execute($params=[]): string{
        if(count($params) != 0){
            return "USELESS PARAMETER\n";
        }
        $report =  "--- bourbaki raw2tmp ---\n";
        $lines = Bourbaki::loadRawFile();
        $N = 0;
        $p = '/(.*?),\s*(\d.*)/';
        $res = [];
        foreach($lines as $line){
            $line = trim($line);
            if($line == ''){
                continue;
            }
            $N++;
            if(!preg_match($p, $line, $m)){
                echo "NO MATCH === $line\n";
            }
            $pages = self::computePages($m[2]);
            $m[1] = str_replace(' 77', '', $m[1]); // fix error of input file : "Van der Waerden 77"
            // family given names
            $fname = trim($m[1]);
            $gname = '';
            $pos = strpos($m[1], '(');
            if($pos){
                $fname = substr($fname, 0, $pos);
                $gname = substr($m[1], $pos+1, -1);
            }
            $res[] = [
                'FNAME' => $fname,
                'GNAME' => $gname,
                'SCORE' => count($pages),
                'PAGES' => $pages,
            ];
        }
        $res = sortByKey::compute($res, 'SCORE');
        //
        $res2 = implode(G5::CSV_SEP, ['FNAME', 'GNAME', 'SCORE', 'PAGES']) . "\n";
        for($i=count($res)-1; $i >= 0; $i--){
            $cur = $res[$i];
            $cur['PAGES'] = implode('+', $cur['PAGES']);
            $res2 .= implode(G5::CSV_SEP, $cur) . "\n";
        }
        //
        $outfile = Bourbaki::tmpFilename();
        file_put_contents($outfile, $res2);
        $report .= "Wrote $N records in $outfile\n";
        return $report;
    }
        
    /**
        Auxiliary of raw2tmp
        @param  $str    Examples :
                        117, 225, 232, 271, 274
                         117, 118, 120 à 124, 127, 128
        @return Array of page numbers
    **/
    public static function computePages($str){
        $parts = explode(',', $str);
        $res = [];
        foreach($parts as $part){
            $part = trim($part);
            $part = str_replace('.', '', $part);
            if(is_numeric($part)){
                // single page number
                // cast to solve bug (a dot sometimes remains ar the end) - not understood
                $res[] = (int)$part;
                continue;
            }
            // page range, like 174-176
            $tmp = explode('à', $part);
            if(count($tmp) != 2){
                echo "ERROR in computePages($str) : $part\n";
                continue;
            }
            [$p1, $p2] = $tmp;
            // cast to solve bug (a dot sometimes remains ar the end) - not understood
            $p1 = (int)$p1;
            $p2 = (int)$p2;
            for($p=$p1; $p <= $p2; $p++){
                $res[] = $p;
            }
        }
        return $res;
    }
    
}// end class
