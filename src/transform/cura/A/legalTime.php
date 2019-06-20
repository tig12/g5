<?php
/********************************************************************************
    Restores legal time and timezone information to cura files.
    Tries to extract timezone offset from date to restore legal time as written in registries.
    Adds a field "DATE_C" (DATE_C = date corrected) to 5-tmp/cura-csv files (overwrites files).
    
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
        $filename = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        $rows1 = csvAssociative::compute($filename);
        $keys1 = array_keys($rows1[0]);
        $keys2 = ['NUM', 'FNAME', 'GNAME', 'OCCU', 'DATE', 'DATE_C', 'PLACE', 'CY', 'C2', 'LG', 'LAT'];
        
        $res .= implode(G5::CSV_SEP, $keys2) . "\n";
        
        $n_total = $n_corrected = 0;
        
        foreach($rows1 as $row1){
            // $row2 = $row1 corrected
            // initialize $row2 = $row1 with DATE_C added after column DATE
            $n_total++;
            $row2 = [];
            foreach($keys1 as $k){
                $row2[$k] = $row1[$k];
                if($k == 'DATE'){
                    $row2['DATE_C'] = '';
                }
            }
            
            if($row1['CY'] != 'FR'){
                // no restoration for foreign countries
                // @todo implement
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            
            [$offset2, $err, $case] = offset_fr::compute($row1['DATE'], $row1['LG'], $row1['C2'], 'HH:MM:SS');

            if($err != ''){
                // no restoration
                // @todo log or add error retransmission ?
                $res .= implode(G5::CSV_SEP, $row2) . "\n";
                continue;
            }
            // if t1 = $row1['DATE'] and t2 = searched date = $row2['DATE_C'] :
            // ut1 = t1 - offset1 ; ut2 = t2 - offset2
            // ut1 = ut2
            // => t1 - offset1 = t2 - offset2
            // => t2 = t1 + offset2 - offset1 = t1 + delta
            $offset1 = substr($row1['DATE'], -6);
            $offset1seconds = HHMMSS2seconds::compute($offset1);
            $offset2seconds = HHMMSS2seconds::compute($offset2);
            $delta = $offset2seconds - $offset1seconds;
            $abs = abs($delta);
//            if($offset2 != $offset1){
            if($abs != 0){
                // DATE_C = DATE with hour and offset modified
                $t = new \DateTime($row1['DATE']);
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
                $row2['DATE_C'] = $t->format('Y-m-d H:i') . $offset2;
            }
            else{
                // Here $offset1 = $offset2
                // As $offset1 is always 0h or -1h, it means that birth time does not include seconds
                // due to longitude computation => seconds can be removed from birth time
                // Can also be removed from offset (because it is 0h or -1h)
                $row2['DATE_C'] = substr($row2['DATE'], 0, 16) . substr($offset2, 0, -3);
            }
            $n_corrected++;
            $res .= implode(G5::CSV_SEP, $row2) . "\n";
        }
        file_put_contents($filename, $res);
        $p = round($n_corrected * 100 / $n_total, 2);
        $report .= "$datafile : restored $n_corrected / $n_total dates ($p %)\n";
        return $report;
    }
}// end class

