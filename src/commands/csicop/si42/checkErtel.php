<?php
/********************************************************************************
    Comparison between si42 and Ertel4391
    
    @license    GPL
    @history    2019-11-24 05:10:54+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\ertel\ertel4391\Ertel4391;
use g5\commands\gauq\LERRCP;
use tiglib\arrays\csvAssociative;

class checkErtel implements Command {
    
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
        $si42file = SI42::tmpFilename();
        $si42 = csvAssociative::compute($si42file);
        $d10 = LERRCP::loadTmpFile_num('D10');
        
        $report =  "Check dates Ertel4391 / $si42file\n";
        
        $nDates = 0;
        foreach($ertel as $rowE){

            $CSINR = $rowE['CSINR'];
            if($CSINR == '' || $CSINR == '0'){
                continue;
            }
            
            $GQID = $rowE['GQID'];
//////// TODO These tweaks must be removed - handled in tweak.csv /////////////////////
            $NUM = substr($GQID, 4); // remove 'D10-'
            if($CSINR == '394'){
                $i42 = 394; // Williams R.
            }
            else if($CSINR == '395'){
                $i42 = 395; // Williams T.
            }
            else if($CSINR == '396'){
                $i42 = 393; // Williams B.
            }
            else{
                $i42 = $CSINR - 1;
            }
            $rowS = $si42[$i42];
            $rowD10 = $d10[$NUM];
            
            $NR    = $rowE['NR'];
            $DATEE = $rowE['DATE'];
            $CSINR = $rowE['CSINR'];
            $nameE = $rowE['FNAME'] . ' ' . $rowE['GNAME'];
            
            $nameS = $rowS['FNAME'] . ' ' . $rowS['GNAME'];
            $DATES = $rowS['DATE'];
            
            $nameD10 = $rowD10['FNAME'] . ' ' . $rowD10['GNAME'];
            $DATED10 = substr($rowD10['DATE'], 0, 10);
            
            if($DATEE != $DATES){
                $nDates++;
                $report .= "\n"
                         . "si42  $DATES $nameS i42 = $i42\n"
                         . "ertel $DATEE $nameE CSINR = $CSINR\n"
                         . "D10   $DATED10 $nameD10 NUM = $NUM\n";
            }
            
        }
        $report .= "$nDates differences\n";
        return $report;
    }
    
}// end class
