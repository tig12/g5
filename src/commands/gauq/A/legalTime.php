<?php
/********************************************************************************
    Partly restores legal time and timezone information to cura files of serie A.
    Tries to extract timezone offset from date to restore legal time as written in registries.
    Adds a field "DATE-C" (DATE-C = date corrected) to data/tmp/gauq/lerrcp files (overwrites files).
    
    @pre        raw2csv and addGeo must have been executed.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-01 03:46:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use tiglib\time\HHMMSS2seconds;
use tiglib\timezone\offset_fr;

class legalTime implements Command {
    
    /** 
        Called by : php run-g5.php gauq <datafile> legalTime
        
        @param $params array containing two elements :
                       - the datafile to process.
                       - 'legalTime' (useless)
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter for legalTime {$params[2]}\n";
        }
        $datafile = $params[0];
        
        $report = '';
        $res = implode(G5::CSV_SEP, A::TMP_FIELDS) . "\n";
        
        $filename = LERRCP::tmpFilename($datafile);
        $rows1 = LERRCP::loadTmpFile($datafile);
        
        $N = $nCorrected = 0;
        
        foreach($rows1 as $row1){
            $N++;
            $row2 = $row1;
            
            if($row1['CY'] != 'FR'){
                // no restoration for other countries - TODO implement
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            
            [$offset, $err, $case] = offset_fr::compute($row1['DATE-UT'], $row1['LG'], $row1['C2'], 'HH:MM:SS');
            if($err != ''){
                // no restoration
                // $case (error code) is stored in $row2['NOTES-DATE'] in tmp file, and will be used by tmp2db
                $row2['NOTES-DATE'] = $case;
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            
            // Compute $row2['DATE-C'] (restored legal time)
            // define ut = $row1['DATE-UT'] and t2  = $row2['DATE-C'] :
            // ut = t2 - offset => t2 = ut + offset
            $offsetSeconds = HHMMSS2seconds::compute($offset);
            $t = new \DateTime($row1['DATE-UT']);
            $interval = new \DateInterval('PT' . abs($offsetSeconds) . 'S');
            if($offsetSeconds < 0){
                $interval->invert = 1;
            }
            $t->add($interval);
            if(substr($offset, -3) == ':00'){
                // don't include useless seconds
                $offset = substr($offset, 0, -3);
                $row2['DATE-C'] = $t->format('Y-m-d H:i');
            }
            else{
                $row2['DATE-C'] = $t->format('Y-m-d H:i:s');
            }
            $row2['TZO'] = $offset;
            //
            // TODO add a function fix_legalTime() to fix DATE-C when it include seconds
            // ex: A1-2 André Georges
            // DATE-UT = 1889-08-13 12:20:40
            // DATE-C = 1889-08-13 12:30:04
            // DATE-C should be converted to 1889-08-13 12:30
            // Then TZO and DATE-UT converted again
            //
            $nCorrected++;
            $res .= implode(G5::CSV_SEP, $row2) . "\n";
        }
        file_put_contents($filename, $res);
        $p = round($nCorrected * 100 / $N, 2);
        $miss = $N - $nCorrected;
        $report .= "$datafile : restored $nCorrected / $N dates ($p %) - miss $miss\n";
        return $report;
    }
    
} // end class
