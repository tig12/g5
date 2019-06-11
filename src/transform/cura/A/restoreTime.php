<?php
/********************************************************************************
    Restores timezone information to cura files.
    Tries to extract timezone offset from date to restore legal time as written in registries.
    Adds a field "DATE_C" to 5-tmp/cura-csv files
    (DATE_C = date corrected)
    
    @pre        raw2csv must have been executed (5-tmp/cura-csv/ must exist).
    
    @license    GPL
    @history    2019-06-01 03:46:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\Full;
use g5\transform\cura\CuraRouter;
use tiglib\time\HHMM2minutes;
use tiglib\arrays\csvAssociative;

class restoreTime implements Command{
    
    const POSSIBLE_PARAM = ['update', 'echo'];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura A restoreTime
        
        @param $params array containing one element : the datafile to process.
        @return String report
    **/
    public static function execute($params=[]): string{
        if(count($params) != 1){
            return "WRONG USAGE : restoreTime must be called with only one parameter\n";
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
    public static function computeOneFile($datafile){
        $report = '';
        $res = '';
        $rows1 = csvAssociative::execute(Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv');
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
            [$dtu2, $err] = \TZ_fr::offset($row1['DATE'], $row1['LG'], $row1['C2']);
            if($err != ''){
                // no restoration
                // @todo log or add error retransmission ?
                $res .= implode(G5::CSV_SEP, $row2);
                continue;
            }
            $dtu1 = substr($row1['DATE'], -6);
            $dtu1minuts = HHMM2minutes::compute($dtu1);
            $dtu2minuts = HHMM2minutes::compute($dtu2);
var_dump($dtu2minuts);
            $delta = $dtu1minuts - $dtu2minuts;
            $abs = abs($delta);
echo "old = $dtu1 $dtu1minuts\n";
echo "new = $dtu2 $dtu2minuts\n";
echo "delta = $delta\n";
            if($dtu2 != $dtu1){
                // DATE_C = DATE with hour and dtu modified
                $t = new \DateTime($row1['DATE']);
                $interval = new \DateInterval('PT' . $abs . 'M');
                if($delta > 0){
                    $t->add($interval);
                }
                else{
                    $t->sub($interval);
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

