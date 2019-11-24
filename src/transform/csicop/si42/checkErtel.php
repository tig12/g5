<?php
/********************************************************************************

    @license    GPL
    @history    2019-11-24 05:10:54+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\si42;

use g5\G5;
use g5\patterns\Command;
use g5\transform\newalch\ertel4391\Ertel4391;
use tiglib\arrays\csvAssociative;

class checkErtel implements Command{
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch muller1083 look gnr
    **/
    const POSSIBLE_PARAMS = [
        'date',
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
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        
        $method = 'check_' . $param;
        
        if(count($params) > 1){
            array_shift($params);
            return self::$method($params);
        }
        
        return self::$method();
    }
    
    // ******************************************************
    private static function check_date(){
        $ertel = Ertel4391::loadTmpFile();
        $si42file = SI42::tmp_filename();
        $si42 = csvAssociative::compute($si42file);
        
        $report =  "Check dates Ertel4391 / $si42file\n";
        
        $nDates = 0;
        foreach($ertel as $rowE){
            $CSINR = $rowE['CSINR'];
            if($CSINR == '' || $CSINR == '0'){
                continue;
            }
            $GNUM = $rowE['GNUM'];
            $NR = $rowE['NR'];
            $DATEE = $rowE['DATE'];
            $CSINR = $rowE['CSINR'];
            $nameE = $rowE['FNAME'] . ' ' . $rowE['GNAME'];
            
            $rowS = $si42[$CSINR - 1];
            $nameS = $rowS['FNAME'] . ' ' . $rowS['GNAME'];
            $DATES = $rowS['DATE'];
            
            if($DATEE != $DATES){
                $nDates++;
                $report .= "\n$DATEE $nameE $CSINR\n$DATES $nameS\n";
            }
        }
        $report .= "$nDates differences\n";
        return $report;
    }
    
    // ******************************************************
    private static function check_missing(){
        $ertel = Ertel4391::loadTmpFile();
        $si42file = SI42::tmp_filename();
        $si42 = csvAssociative::compute($si42file);
        
        $report =  "Check missing Ertel4391 / $si42file\n";
        
        $nMiss = 0;
        foreach($ertel as $rowE){
            $CSINR = $rowE['CSINR'];
            if($CSINR == '' || $CSINR == '0'){
                continue;
            }
            $GNUM = $rowE['GNUM'];
            $NR = $rowE['NR'];
            $DATEE = $rowE['DATE'];
            $CSINR = $rowE['CSINR'];
            $nameE = $rowE['FNAME'] . ' ' . $rowE['GNAME'];
            
            $rowS = $si42[$CSINR - 1];
            $nameS = $rowS['FNAME'] . ' ' . $rowS['GNAME'];
            $DATES = $rowS['DATE'];
            
            if($DATEE != $DATES){
                $nDates++;
                $report .= "\n$DATEE $nameE $CSINR\n$DATES $nameS\n";
            }
        }
        $report .= "$nDates differences\n";
        return $report;
    }
    
}// end class
