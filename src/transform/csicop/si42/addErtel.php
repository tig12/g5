<?php
/********************************************************************************
    Adds information coming from Ertel4391 to 5-csicop/408-csicop-si42.csv
    
    
    
    @license    GPL
    @history    2019-11-23 11:18:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\si42;

use g5\G5;
use g5\patterns\Command;
use g5\transform\newalch\ertel4391\Ertel4391;
use tiglib\arrays\csvAssociative;

class addErtel implements Command{
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $ertel = Ertel4391::loadTmpFile();
        $si42file = SI42::tmp_filename();
        $si42 = csvAssociative::compute($si42file);
        
        $report =  "Add Ertel4391 to $si42file\n";
        
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
                echo "\n$DATEE $nameE $CSINR\n$DATES $nameS\n";
            }
            
//echo "\n<pre>"; print_r($rowE); echo "</pre>\n"; exit;
        }
exit;
        $outfile = $infile;
        file_put_contents($outfile, $res);
        $report .=  "$n records were marked as canvas 1\n";
        return $report;
    }
    
    
}// end class    

