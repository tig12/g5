<?php
/********************************************************************************
    Adds columns GQID ERID CPID in data/tmp/cfepp/cfepp-1120-nienhuys.csv
    Uses Ertel 4391 file.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-27 23:30:58+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\model\Person;
use g5\commands\cfepp\final3\Final3;
use g5\commands\ertel\Ertel;
use g5\commands\ertel\sport\ErtelSport;
use tiglib\patterns\Command;

class ids implements Command {
    
    /** 
        @param $param Empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : {$params[1]}\n";
        }
        
        $report =  "--- cfepp final3 ids ---\n";                                                                     
        
        // Array to complete: add values in columns ERID GQID CPID
        $final3 = Final3::loadTmpFile_cfid();
        
        // data/tmp/ertel/ertel-4384-sport.csv : 4384 rows
        $ertelfile = ErtelSport::loadTmpFile();

        $NCFEPP = $NnotCFEPP = 0;
        foreach($ertelfile as $cur){
            $CFID = $cur['CFEPNR'];
            if($CFID == ''){
                $NnotCFEPP++;
                continue;
            }
            $NCFEPP++;
            $final3[$CFID]['ERID'] = $cur['NR'];
            $final3[$CFID]['GQID'] = ErtelSport::GQIDfrom3a_sports($cur);
            $final3[$CFID]['CPID'] = $cur['PARA_NR'];
        }
        
        // store modified tmp file 
        $outfile = Final3::tmpFilename();
        $out = implode(G5::CSV_SEP, Final3::TMP_FIELDS) . "\n";
        foreach($final3 as $line){
            $out .= implode(G5::CSV_SEP, $line) . "\n";
        }
        file_put_contents($outfile, $out);
        
        $report .=  "Added ids to $NCFEPP records in $outfile\n";
        return $report;
    }
    
} // end class
