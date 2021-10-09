<?php
/********************************************************************************
    Adds information coming from Ertel4391 to 5-csicop/408-csicop-si42.csv
    
    
    
    @license    GPL
    @history    2019-11-23 11:18:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\ertel\sport\Ertel4391;
use tiglib\arrays\csvAssociative;

class addErtel implements Command {
    
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
        $si42file = SI42::tmpFilename();
        $si42 = csvAssociative::compute($si42file);
        
        $report =  "Add Ertel4391 to $si42file\n";
        
        $nDates = 0;
        foreach($ertel as $rowE){
            
            $CSINR = $rowE['CSINR'];
            $GNUM = $rowE['GNUM'];
            
            if($CSINR == '' || $CSINR == '0'){
                continue;
            }
            
            $i42 = $CSINR - 1;
            
            $rowS = $si42[$i42];
            
            $NR = $rowE['NR'];
            $DATEE = $rowE['DATE'];
            $CSINR = $rowE['CSINR'];
            $nameE = $rowE['FNAME'] . ' ' . $rowE['GNAME'];
            
            $rowS = $si42[$CSINR - 1];
            
            $nameS = $rowS['FNAME'] . ' ' . $rowS['GNAME'];
            $DATES = $rowS['DATE'];
            
            if($DATEE != $DATES){
                echo "\nertel $DATEE $nameE CSINR = $CSINR\nsi42  $DATES $nameS i42 = $i42\n";
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

