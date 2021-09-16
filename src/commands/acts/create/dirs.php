<?php
/********************************************************************************
    
    @license    GPL
    @history    2019-12-23 11:46:39+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\acts\create;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\cura\Cura;
use tiglib\arrays\csvAssociative;
use g5\commands\acts\Acts;

class dirs implements Command { 
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch muller1083 look gnr
    **/
    const POSSIBLE_PARAMS = [
        'y' => "Create directories YYYY/MM/DD for a date range\nex: run-g5.php acts create dirs years 1792 2020",
    ];
    
    const EX = [
        'y' => 'run-g5.php acts create dirs years 1792 2020',
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
        
        $method = 'exec_' . $param;
        
        if(count($params) > 1){
            array_shift($params);
            return self::$method($params);
        }
        
        return self::$method();
    }
    
    // ******************************************************
    /**
    **/
    private static function exec_y($params=[]){
        $report = '';
        if(count($params) != 2){
            $report .= "Need 2 parameters : year1 and year2\n";
            $report .= self::EX['y'] . "\n";
            return $report;
        }
        // @todo check YYYY format
        $y1 = $params[0];
        $y2 = $params[1];
        $dir = Acts::$DIR;
        $nDiff = 0;
        for($y=$y1; $y <= $y2; $y++){
            for($m=1; $m <= 12; $m++){
                $ndays = cal_days_in_month(CAL_GREGORIAN, $m, $y);
                for($d=1; $d <= $ndays; $d++){
                    $dir =  Acts::$DIR
                            . DS . $y
                            . DS . str_pad($m , 2, '0', STR_PAD_LEFT)
                            . DS . str_pad($d , 2, '0', STR_PAD_LEFT);
                    // @todo set facl
                    if(!is_dir($dir)){
                        mkdir($dir, 0777, true);
                        $report .= "created $dir\n";
                    }
                }
            }
        }
        $report .= "\n";
        return $report;
    }
    
    // ******************************************************
    /** 
        Compares Irving dates with Ertel4391
    **/
    private static function look_ertel(){
        
        $report = '';
        
        $irving = Irving::loadTmpCsv_csid();
        $ertel = Ertel4391::loadTmpFile();
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