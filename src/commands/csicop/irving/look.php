<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-12-23 11:46:39+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\cura\Cura;
use g5\commands\newalch\ertel4391\Ertel4391;
use g5\commands\csicop\si42\SI42;
use tiglib\arrays\csvAssociative;

class look implements Command { 
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch muller1083 look gnr
    **/
    const POSSIBLE_PARAMS = [
        'ertel' => 'Compare Irving and Ertel',
        'si42'  => 'Compare with file SI42',
    ];
    
    // *****************************************
    /** 
        Routes to the different actions, based on $param
        @param $params Array
                       First element indicates which method execute ; must be one of self::POSSIBLE_PARAMS
                       Other elements are transmitted to the called method.
                       (Called methods are responsible to handle their params).
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "    $k : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $method = 'look_' . $param;
        
        if(count($params) > 1){
            array_shift($params);
            return self::$method($params);
        }
        
        return self::$method();
    }
    
    // ******************************************************
    /**
        Shows the differences of dates between Irving and SI42.
        Differences on dates is done by look_date(), but the purpose of this function
        was to fix the differences of ids between SI42 and Irving.
        The output of this function was used to build Irving::IRVING_SI42
    **/
    private static function look_si42(){
        $si42 = csvAssociative::compute(SI42::tmpFilename(), G5::CSV_SEP);
        $irving = @Irving::loadTmpFile_csid();
        if(empty($irving)){
            return "ERROR : Missing file " . Irving::tmpFilename() . "\nFirst execute : php run-g5.php csicop irving raw2tmp\n";
        }
        
        $report = '';
        $nDiff = 0;
        foreach($si42 as $srow){
            $sdate = $srow['DATE'];
            $irow = $irving[$srow['CSID']];
            $idate = substr($irow['DATE'], 0, 10);
            if($idate != $sdate){
                $report .= "irving {$irow['CSID']} $idate {$irow['C2']} {$irow['FNAME']} {$irow['GNAME']}\n";
                $report .= "si42   {$srow['CSID']} $sdate {$srow['C2']} {$srow['FNAME']} {$srow['GNAME']}\n";
                $report .= "\n";
                $nDiff++;
            }
        }
        $report .= "$nDiff differences\n";
        
        return $report;
    }
    
    // ******************************************************
    /**  Compares Irving dates with Ertel4391 **/
    private static function look_ertel(){
        
        $report = '';
        
        $irving = @Irving::loadTmpFile_csid();
        if(empty($irving)){
            return "ERROR : Missing file " . Irving::tmpFilename() . "\nFirst execute : php run-g5.php csicop irving raw2tmp\n";
        }
        $ertel = @Ertel4391::loadTmpFile();
        if(empty($ertel)){
            return "ERROR : Missing file " . Ertel4391::tmpFilename() . "\nFirst import this file\n";
        }
        $nOK = $nDiff = 0;
        foreach($ertel as $erow){
            $CSID = $erow['CSINR'];
            if($CSID == '' || $CSID == 0){
                continue;
            }
            $irow = $irving[$CSID];
            $dateI = substr($irow['DATE'], 0, 10);
            $nameI = $irow['FNAME'] . ' ' . $irow['GNAME'];
            $dateE = $erow['DATE'];
            $nameE = $erow['FNAME'] . ' ' . $erow['GNAME'];
            if($dateI != $dateE){
                $nDiff++;
                $report .= "CSICOP $CSID = Ertel {$erow['NR']}\n";
                $report .= "Irving $dateI $nameI\n";
                $report .= "Ertel  $dateE $nameE\n";
                $report .= "\n";
            }
            else{
                $nOK++;
            }
        }
        $report .= "$nOK records match dates\n";
        $report .= "$nDiff Irving dates different from Ertel 4391\n";
        return $report;
    }
    
}// end class