<?php
/********************************************************************************
    Injects names and birth day of 5-newalch-csv/1083MED.csv to 5-cura-csv/A2.csv E1.csv
    
    @license    GPL
    @history    2019-10-19 12:28:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\G5;
use g5\patterns\Command;
use g5\transform\cura\Cura;

class fixCura implements Command {
    
    // *****************************************
    /** 
        Routes to fix_names() or fix_dates()
        @param $params Array containing 3 strings
                       Param 1 must be 'A2' or 'E1'
                       Param 2 must be 'names' or 'dates'
                       Param 3 must be 'report' or 'update'
'names' => "Copies the values of columns FNAME and GNAME to corresponding records of A2 and E1",
'dates' => "Copies the day part of column DATE to corresponding records of A2 and E1",
    **/
    public static function execute($params=[]): string{
        
        $possible1 = ['A2', 'E1'];
        $possible2 = ['names', 'dates'];
        $possible3 = ['report', 'update'];
        $msg1 = "'" . implode($possible1, "', '") . "'";
        $msg2 = "'" . implode($possible2, "', '") . "'";
        $msg3 = "'" . implode($possible3, "', '") . "'";
        
        if(count($params) < 3){
            return "PARAMETER MISSING - This function needs 3 parameters :\n"
                . "  Param 1 can be : $msg1\n  Param 2 can be : $msg2\n  Param 3 can be : $msg3\n";
        }
        if(count($params) > 3){
            return "USELESS PARAMETER : '{$params[3]}'\n";
        }
        $file = $params[0];
        $method = $params[1];
        $action = $params[2];
        if(!in_array($file, $possible1)){
            return "INVALID PARAMETER '$file'\nPossible values for parameter 1 : $msg1\n";
        }
        if(!in_array($method, $possible2)){
            return "INVALID PARAMETER '$method'\nPossible values for parameter 2 : $msg2\n";
        }
        if(!in_array($action, $possible3)){
            return "INVALID PARAMETER '$action'\nPossible values for parameter 3 : $msg3\n";
        }
        
        $method = "fix_$method";
        return self::$method($file, $action);
    }
    
    
    // ******************************************************
    /**
    **/
    private static function fix_names($file, $action){
        $report = '';
        
        $curaFile = Cura::loadTmpCsv_num($file); // keys = NUM
        
        $mulPrefix = ($file == 'A2' ? 'SA2' : 'ND1');
        $tmp = Muller1083::loadTmpFile();
        $mulFile = []; // keys = NUM
        foreach($tmp as $mulrow){
            $gnr = $mulrow['GNR'];
            if(substr($mulrow['GNR'], 0, 3) == $mulPrefix){
                $mulFile[substr($mulrow['GNR'], 3)] = $mulrow;
            }
        }
        
        if($action == 'update'){
            $res = implode(G5::CSV_SEP, array_keys(current($curaFile))) . "\n";
        }
        
        $nRestoredNames = 0;
        foreach($curaFile as $NUM => $curarow){
            if(!isset($mulFile[$NUM])){
                if($action == 'update'){
                    $res .= implode(G5::CSV_SEP, $curarow) . "\n";
                }
                continue;
            }
            if($curarow['FNAME'] == "Gauquelin-$file-$NUM"){
                $nRestoredNames++;
            }
            $mulrow =& $mulFile[$NUM];
            if($action == 'report'){
                $report .= "\n$file    NUM $NUM\t {$curarow['FNAME']}\t| {$curarow['GNAME']}\n";
                $report .= "Müller NR {$mulrow['NR']}\t {$mulrow['FNAME']}\t| {$mulrow['GNAME']}\n";
            }
            else{
                $new = $curarow;
                // HERE a distinction between official name and name could be done
                $new['FNAME'] = $mulrow['FNAME'];
                $new['GNAME'] = $mulrow['GNAME'];
                $res .= implode(G5::CSV_SEP, $new) . "\n";
            }
        }
        
        $N = count($mulFile);
        if($action == 'report'){
            $report .= "An execution with 'update' will modify $N records\n";
            $report .= "It will restore $nRestoredNames unknown names in $file\n";
        }
        else{
            $destFile = Cura::tmpFilename($file);
            file_put_contents($destFile, $res);
            $report .= "$N lines modified in $destFile\n";
        }
        return $report;
        
    }
    
    // ******************************************************
    /**
    **/
    private static function fix_dates($file, $action){
return "ok fix_dates($file, $action)";
        $report = '';
        // assoc arrays
        $a2s = Cura::loadTmpCsv_num('A2'); // keys = NUM
        $e1s = Cura::loadTmpCsv_num('E1'); // keys = NUM
        $MullerCsv = Muller1083::loadTmpFile_nr(); // keys = NR
    }
    
} // end class
