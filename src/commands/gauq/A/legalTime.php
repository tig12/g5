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
            
            [$offset2, $err, $case] = offset_fr::compute($row1['DATE-UT'], $row1['LG'], $row1['C2'], 'HH:MM:SS');
            if($err != ''){
                // no restoration
                // $case (error code) is stored in tmp file, and will be used by tmp2db
                $row2['NOTES-DATE'] = $case;
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            // if  t1 = $row1['DATE-UT']
            // and t2 = searched date = $row2['DATE-C'] :
            // ut1 = t1 - offset1 ; ut2 = t2 - offset2
            // ut1 = ut2
            // => t1 - offset1 = t2 - offset2
            // => t2 = t1 + offset2 - offset1 = t1 + delta
            $offset1 = substr($row1['DATE-UT'], -6);
            $offset1seconds = HHMMSS2seconds::compute($offset1);
            $offset2seconds = HHMMSS2seconds::compute($offset2);
            $delta = $offset2seconds - $offset1seconds;
            $abs = abs($delta);
            if($abs != 0){ // $offset2 != $offset1
                // DATE = DATE with hour and offset modified
                $t = new \DateTime($row1['DATE-UT']);
                $interval = new \DateInterval('PT' . $abs . 'S');
                if($delta > 0){
                    $t->add($interval);
                }
                else{
                    $t->sub($interval);
                }
                // if $offset2 ends with ':00', can be safely removed
                if(substr($offset2, -3) == ':00'){
                    $offset2 = substr($offset2, 0, -3);
                }
                $row2['DATE-C'] = $t->format('Y-m-d H:i') . $offset2;
            }
            else{
                // Here $offset1 = $offset2
                // As $offset1 is always 0h or -1h, it means that birth time does not include seconds
                // due to longitude computation => seconds can be removed from birth time
                // Can also be removed from offset (because it is 0h or -1h)
                $row2['DATE-C'] = substr($row2['DATE-UT'], 0, 16) . substr($offset2, 0, -3);
            }
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

