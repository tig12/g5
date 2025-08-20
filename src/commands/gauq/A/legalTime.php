<?php
/********************************************************************************
    Partly restores legal time and timezone information to cura files of serie A.
    Modifies files data/tmp/gauq/lerrcp/A*.csv (overwrites files).
    Tries to extract timezone offset from date to restore legal time as written in registries.
    Adds a field "DATE-C" (DATE-C = date corrected) to tmp csv files.
    
    @pre        raw2csv and addGeo must have been executed.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-01 03:46:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use tiglib\time\HHMMSS2seconds;
use tiglib\timezone\offset;

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
            
            if(!offset::isCountryImplemented($row1['CY'])){
                // no timezone restoration, just copy the line
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            
            [$offset, $case, $err] = offset::computeTiglib(
                $row1['CY'],
                $row1['DATE-UT'],
                $row1['LG'],
                $row1['C2'],
                'HH:MM:SS'
            );
            if($offset == ''){
                // no restoration
                // $case (= error code) is stored in $row2['NOTES-DATE'] in tmp file,
                // and will be used by tmp2db to build an issue
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
            $t->add($interval); // $t now represents DATE-UT
            if(substr($offset, -3) == ':00'){
                // Eliminate obviously useless seconds in offset
                $offset = substr($offset, 0, -3);
                $row2['DATE-C'] = $t->format('Y-m-d H:i');
            }
            // Eliminate obviously useless seconds in legal date
            if($t->format('s') == '00' || $t->format('s') == '01'){
                $row2['DATE-C'] = $t->format('Y-m-d H:i');
            }
            else{
                $row2['DATE-C'] = self::fixLegalTime($t);
            }
            $row2['TZO'] = $offset;
            $nCorrected++;
            $res .= implode(G5::CSV_SEP, $row2) . "\n";
        }
        file_put_contents($filename, $res);
        $p = round($nCorrected * 100 / $N, 2);
        $miss = $N - $nCorrected;
        $report .= "$datafile : restored $nCorrected / $N dates ($p %) - miss $miss\n";
        return $report;
    }
    
    /** 
        Rounds the legal time.
        @return     a YYYY-MM-DD HH:MM string expressing the legal time, rounded to the nearest round time.
    **/
    private static function fixLegalTime(\DateTime $t){
        $YMD = $t->format('Y-m-d');
        $min = $t->format('i');
        $hour = $t->format('H');
        $remainder = $min % 10;
        if(in_array($remainder, [7, 8, 9, 0, 1, 2])){
            $min = round($min/10)*10;
        }
        else{
            return $t->format('Y-m-d H:i:s'); // do nothing
        }
        $t->setTime($hour, $min);
        return $t->format('Y-m-d H:i');
    }
    
    
} // end class
