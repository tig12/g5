<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-12-23 11:46:39+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\irving;

use g5\G5;
use g5\patterns\Command;
use g5\transform\cura\Cura;
use g5\transform\newalch\ertel4391\Ertel4391;
use g5\transform\csicop\si42\SI42;
use tiglib\arrays\csvAssociative;

class look implements Command{ 
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch muller1083 look gnr
    **/
    const POSSIBLE_PARAMS = [
        'date' => 'Compare dates between Irving and Ertel, SI42, Gauquelin',
        'si42' => 'Compare with file SI42',
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
        Compares Irving dates with Ertel4391, SI42 an D10
        File    | Ids
        --------+-----
        Irving  | CSID
        SI42    | CSID
        Ertel   | CSID GQID
        D10     | GQID
    **/
    private static function look_date(){
        
        $irving = Irving::loadTmpCsv_csid();
        $d10 = Cura::loadTmpCsv_num('D10');
        $ertel = Ertel4391::loadTmpFile();
        $ertel_csid = [];
        $ertel_gqid = [];
        foreach($ertel as $row){
            $CSID = $row['CSINR'];
            if($CSID == '' || $CSID == 0){
                continue;
            }
            $ertel_csid[$CSID] = $row;
            $ertel_gqid[$row['G_NR']] = $row;
        }
        $si42 = csvAssociative::compute(SI42::tmp_filename(), G5::CSV_SEP);
        foreach($si42 as $row){
            $si42_csid[$row['CSID']] = $row;
        }
        
        $nOK = $nDiffS = $nDiffE = $nDiffG = 0; // nb of different dates between Irving and other files
        $n = 0;
        $report = '';
        foreach($irving as $CSID => $irow){
            // test on Ertel because it links with D10
            if(!isset($ertel_csid[$CSID])){
                continue;
            }
            $n++;
            $CSID = $irow['CSID'];
            $dateI = substr($irow['DATE'], 0, 10);
            $nameI = $irow['FNAME'] . ' ' . $irow['GNAME'];
            $dateS = $si42_csid[$CSID]['DATE'];
            $nameS = $si42_csid[$CSID]['FNAME'] . ' ' . $si42_csid[$CSID]['GNAME'];
            $dateE = $ertel_csid[$CSID]['DATE'];
            $nameE = $ertel_csid[$CSID]['FNAME'] . ' ' . $ertel_csid[$CSID]['GNAME'];
            $NUM = str_replace('D10-', '', $ertel_csid[$CSID]['GNUM']);
            $dateG = substr($d10[$NUM]['DATE'], 0, 10);
            $nameG = $d10[$NUM]['FNAME'] . ' ' . $d10[$NUM]['GNAME'];
            $diffS = $dateI != $dateS;
            $diffE = $dateI != $dateE;
            $diffG = $dateI != $dateG;
            //if(true){
            if($diffE || $diffS || $diffG){
                $report .= "CSICOP $CSID = Ertel {$ertel_csid[$CSID]['NR']} = D10 $NUM\n";
                $report .= "Irving $dateI $nameI\n";
                $report .= "SI42   $dateS $nameS\n";
                $report .= "Ertel  $dateE $nameE\n";
                $report .= "D10    $dateG $nameG\n";
                $report .= "\n";
                if($diffS) $nDiffS++;
                if($diffE) $nDiffE++;
                if($diffG) $nDiffG++;
            }
            else{
                $nOK++;
            }
        }
        $report .= "------------------\n";
        $report .= "$n records analyzed\n";
        $report .= "$nOK records match dates in the 4 files\n";
        $report .= "$nDiffS Irving dates different from S.I. 4.2\n";
        $report .= "$nDiffE Irving dates different from Ertel 4391\n";
        $report .= "$nDiffG Irving dates different from D10\n";
        
        return $report;
    }
    
    // ******************************************************
    /**
        @param $
    **/
    private static function look_si42(){
        $si42 = csvAssociative::compute(SI42::tmp_filename(), G5::CSV_SEP);
        $irving = Irving::loadTmpCsv_csid();
        
        $report = '';
        foreach($si42 as $srow){
            $irow = $irving[$srow['CSID']];
            $idate = substr($irow['DATE'], 0, 10);
            $sdate = $srow['DATE'];
            if($idate != $sdate){
                $report .= "si42   {$srow['CSID']} $sdate {$srow['C2']} {$srow['FNAME']} {$srow['GNAME']}\n";
                $report .= "irving {$irow['CSID']} $idate {$irow['C2']} {$irow['FNAME']} {$irow['GNAME']}\n";
                $report .= "\n";
            }
        }
        
        return $report;
    }
}// end class