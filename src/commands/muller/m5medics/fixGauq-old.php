<?php
/********************************************************************************
    
    THIS VERSION IS NOT USED ANYMORE
    
    THIS VERSION MODIFIES FILES IN data/tmp/
    
    NEW VERSION MODIFIES DATABASE

    Injects names and birth day of
        data/tmp/muller/5-medics/muller5-1083-medics.csv
    to
        data/tmp/gauq/lerrcp/A2.csv
        data/tmp/gauq/lerrcp/E1.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-10-19 12:28:40+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m5medics;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;

class fixGauq implements Command {
    
    // *****************************************
    /** 
        Routes to fix_names() or fix_dates()
        @param $params Array containing 3 strings
                       Param 1 must be 'A2' or 'E1'
                       Param 2 must be 'names' or 'days'
                       Param 3 must be 'report' or 'update'
    **/
    public static function execute($params=[]): string{
        
        $possible1 = ['A2', 'E1'];
        $possible2 = ['names', 'days'];
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
    private static function fix_names($file, $action){
        $report = '';
        
        $gauqFile = LERRCP::loadTmpCsv_num($file); // keys = NUM
        
        $mulPrefix = ($file == 'A2' ? 'SA2' : 'ND1');
        
        // $mulFile contains only records common with A2 and E1
        $tmp = M5medics::loadTmpFile();
        $mulFile = []; // keys = NUM
        foreach($tmp as $mulrow){
            $gnr = $mulrow['GNR'];
            if(substr($mulrow['GNR'], 0, 3) == $mulPrefix){
                $mulFile[substr($mulrow['GNR'], 3)] = $mulrow;
            }
        }
        
        if($action == 'update'){
            $res = implode(G5::CSV_SEP, array_keys(current($gauqFile))) . "\n";
        }
        
        $nRestoredNames = 0;
        foreach($gauqFile as $NUM => $gauqRow){
            if(!isset($mulFile[$NUM])){
                if($action == 'update'){
                    $res .= implode(G5::CSV_SEP, $gauqRow) . "\n";
                }
                continue;
            }
            if($gauqRow['FNAME'] == "Gauquelin-$file-$NUM"){
                $nRestoredNames++;
            }
            $mulrow =& $mulFile[$NUM];
            if($action == 'report'){
                $report .= "\n$file    NUM $NUM\t {$gauqRow['FNAME']}\t| {$gauqRow['GNAME']}\n";
                $report .= "Müller NR {$mulrow['NR']}\t {$mulrow['FNAME']}\t| {$mulrow['GNAME']}\n";
            }
            else{
                $new = $gauqRow;
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
            $destFile = LERRCP::tmpFilename($file);
            file_put_contents($destFile, $res);
            $report .= "$N records modified in $destFile\n";
            $report .= "$nRestoredNames unknown names restored in $file\n";
        }
        return $report;
        
    }
    
    // ******************************************************
    private static function fix_days($file, $action){
        $report = '';
        
        $gauqFile = LERRCP::loadTmpCsv_num($file); // keys = NUM
        
        $mulPrefix = ($file == 'A2' ? 'SA2' : 'ND1');
        
        // $mulFile contains only records common with A2 and E1
        $tmp = M5medics::loadTmpFile();
        $mulFile = []; // keys = NUM
        foreach($tmp as $mulrow){
            $gnr = $mulrow['GNR'];
            if(substr($mulrow['GNR'], 0, 3) == $mulPrefix){
                $mulFile[substr($mulrow['GNR'], 3)] = $mulrow;
            }
        }
        
        if($action == 'update'){
            $res = implode(G5::CSV_SEP, array_keys(current($gauqFile))) . "\n";
        }
        
        $nDateFixed = 0;
        foreach($gauqFile as $NUM => $gauqRow){
            if(!isset($mulFile[$NUM])){
                if($action == 'update'){
                    $res .= implode(G5::CSV_SEP, $gauqRow) . "\n";
                }
                continue;
            }
            $mulrow =& $mulFile[$NUM];
            $mulday = substr($mulrow['DATE'], 0, 10);
            $curaday = substr($gauqRow['DATE'], 0, 10);
            
            $new = $gauqRow;
            
            if($mulday != $curaday){
                $nDateFixed++;
                // just replace the day, keep hour unchanged
                $new['DATE'] = $mulday . substr($gauqRow['DATE'], 10);
                if($action == 'report'){
                    $report .= "\n$file    NUM $NUM\t {$gauqRow['DATE']} {$gauqRow['FNAME']}\t| {$gauqRow['GNAME']}\n";
                    $report .= "Müller NR {$mulrow['NR']}\t {$mulrow['DATE']}\t   {$mulrow['FNAME']}\t| {$mulrow['GNAME']}\n";
                    $report .= "Correction       {$new['DATE']}\n";
                }
            }
            
            if($action == 'update'){
                $res .= implode(G5::CSV_SEP, $new) . "\n";
            }
        }
        
        if($action == 'report'){
            $report .= "An execution with 'update' will modify $nDateFixed records\n";
        }
        else{
            $destFile = LERRCP::tmpFilename($file);
            file_put_contents($destFile, $res);
            $report .= "$nDateFixed records modified in $destFile\n";
        }
        return $report;
    }
    
} // end class
