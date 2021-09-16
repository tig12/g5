<?php
/********************************************************************************
    Adds field CANVAS to data/tmp/csicop/si42/408-csicop-si42.csv
    Fills CANVAS with value "1" for 128 records, listed in si42-p41.txt
    
    @license    GPL
    @history    2019-11-18 21:37:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\si42;

use g5\G5;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;

class addCanvas1 implements Command {
    
    // *****************************************
    /** 
        WARNING Execution of this command lead to modify SI42::rawFilename_canvas1() :
        Changed "Brown, R.S." to "Brown, R." - A priori ok, state and mars sector correspond
    
        @param  $params Empty array
        @return String report
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = SI42::tmpFilename();
        if(!is_file($infile)){
            return "ERROR : file $infile does not exist - run import and start again\n";
        }
        $c1file = SI42::rawFilename_canvas1();
        
        $report =  "--- CSICOP si42 addCanvas1 ---\n";
        
        $c1rows = file($c1file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        
        $res = implode(G5::CSV_SEP, SI42::TMP_FIELDS) . G5::CSV_SEP . "CANVAS\n";
        
        $rows = csvAssociative::compute($infile);
        $n = 0;
        $matched = [];
        foreach($rows as $row){
            $fname = $row['FNAME'];
            $name = $row['FNAME'] . ', ' . $row['GNAME'];
            $found = false;
            foreach($c1rows as $candidate){
                if($candidate == $fname || $candidate == $name){
                    $found = true;
                    $matched[] = $candidate;
                    break;
                }
            }
            if($fname == 'Mann' && $row['C2'] == 'CA'){
                // particular case that could not be solved by adding given name in si42-p41.txt
                // because there are 2 Mann R. One is from Utah, one from Califormnia
                // First canvas included the Mann from Utah
                $found = false;
            }
            if($fname == 'Taylor' && $row['MA12'] == '8'){
                // particular case that could not be solved by adding given name in si42-p41.txt
                // because there are 2 Taylor B. from New Jersey
                // First canvas included the Taylor with mars sector = 3
                $found = false;
            }
            
            $new = $row;
            if($found){
                $new['CANVAS'] = '1';
                $n++;
            }
            else{
                $new['CANVAS'] = '';
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        $unmatched = array_diff($c1rows, $matched);
        if(count($unmatched) != 0){
            $report .= "Unmatched :\n";
            $report .= print_r($unmatched, true);
        }
        $outfile = $infile;
        file_put_contents($outfile, $res);
        // Here the report MUST display 128 ; more => remains doublons
        $report .=  "$n records were marked as canvas 1 in $outfile\n";
        return $report;
    }
    
    
}// end class    

