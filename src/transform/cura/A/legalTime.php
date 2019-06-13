<?php
/********************************************************************************
    Restores legal time and timezone information to cura files.
    Tries to extract timezone offset from date to restore legal time as written in registries.
    Adds a field "DATE_C" to 5-tmp/cura-csv files
    (DATE_C = date corrected)
    
    @pre        raw2csv must have been executed.
    
    @license    GPL
    @history    2019-06-01 03:46:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\Full;
use g5\transform\cura\CuraRouter;
use tiglib\time\HHMMSS2seconds;
use tiglib\arrays\csvAssociative;
use tiglib\timezone\offset_fr;

class legalTime implements Command{
    
    const POSSIBLE_PARAM = ['update', 'echo'];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura A legalTime
        
        @param $params array containing two elements :
                       - the datafile to process.
                       - 'legalTime' (useless)
        @return String report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter for legalTime {$params[2]}\n";
        }
        $datafiles = CuraRouter::computeDatafiles($params[0]);
        $report = '';
        foreach($datafiles as $datafile){
            $report .= self::computeOneFile($datafile);
        }
        return $report;
    }
    
    
    // ******************************************************
    /**
        @param $datafile String like 'A1', 'A2' ... 'A6'
        @return String report
    **/
    private static function computeOneFile($datafile){
        $report = '';
        $res = '';
        $rows1 = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv');
        $keys1 = array_keys($rows1[0]);
        $keys2 = ['NUM', 'FNAME', 'GNAME', 'OCCU', 'DATE', 'DATE_C', 'PLACE', 'CY', 'C2', 'LG', 'LAT'];
        $res .= implode(G5::CSV_SEP, $keys2) . "\n";
        
$i = 0;
        foreach($rows1 as $row1){
            // initialize $newrow
            $row2 = [];
echo "\n" . 'DATE = ' . $row1['CY'] . ' - ' . $row1['DATE'] . "\n";
            foreach($keys1 as $k){
                $row2[$k] = $row1[$k];
                if($k == 'DATE'){
                    $row2['DATE_C'] = '';
                }
            }
            
            if($row1['CY'] != 'FR'){
                // no restoration for foreign countries
                // @todo implement
                $res .= implode(G5::CSV_SEP, $row2);
                continue;
            }
            [$dtu2, $err] = offset_fr::compute($row1['DATE'], $row1['LG'], $row1['C2']);
            if($err != ''){
                // no restoration
                // @todo log or add error retransmission ?
                $res .= implode(G5::CSV_SEP, $row2);
                continue;
            }
            $dtu1 = substr($row1['DATE'], -6);
            $dtu1seconds = HHMMSS2seconds::compute($dtu1);
            $dtu2seconds = HHMMSS2seconds::compute($dtu2);
            $delta = $dtu1seconds - $dtu2seconds;
            $abs = abs($delta);
echo "old = $dtu1 ; $dtu1seconds\n";
echo "new = $dtu2 ; $dtu2seconds\n";
echo "delta = $delta\n";
            if($dtu2 != $dtu1){
                // DATE_C = DATE with hour and dtu modified
                $t = new \DateTime($row1['DATE']);
                $interval = new \DateInterval('PT' . $abs . 'S');
                if($delta > 0){
                    $t->sub($interval);
                }
                else{
                    $t->add($interval);
                }
                $row2['DATE_C'] = $t->format('Y-m-d H:i') . $dtu2;
            }
            else{
                // DATE_C = DATE
                $row2['DATE_C'] = substr($row2['DATE'], 0, 16) . $dtu2;
            }
echo 'DATE_C = ' . $row2['DATE_C'] . "\n";
echo "\n<pre>"; print_r($row2); echo "</pre>\n";
$i++; if($i > 1)break;
        }
        return $report;
    }
}// end class

