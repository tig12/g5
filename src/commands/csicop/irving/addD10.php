<?php
/********************************************************************************
    Add fields GQID to data/tmp/csicop/irving/408-csicop-irving.csv
    
    Uses Ertel file (after correctionn on field CSID) to build the correspondance
    See https://tig12.github.io/gauquelin5/csicop.html
    
    @license    GPL
    @history    2019-12-24 09:51:10+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\gauq\Cura;
use g5\commands\ertel\sport\Ertel4391;

class addD10 implements Command {

    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Irving::tmpFilename();
        
        $report =  "--- csicop irving addD10 ---\n";
        
        $irving = Irving::loadTmpFile_csid();
        $d10 = Cura::loadTmpFile_num('D10');
        $ertel = Ertel4391::loadTmpFile();
        $ertel_csid = [];
        $ertel_gqid = [];
        foreach($ertel as $row){
            $CSID = $row['CSINR'];
            // Fix Ertel file (records in Csicop without CSID)
            if($row['NR'] == 2872){
                $CSID = 254; // Miller Freddie 1911-04-03
            }
            else if($CSID == '' || $CSID == 0){
                continue;
            }
            $ertel_csid[$CSID] = $row;
            $ertel_gqid[$row['G_NR']] = $row;
        }
        
        $output = implode(G5::CSV_SEP, Irving::TMP_FIELDS) . "\n";
        $nModif = 0;
        foreach($irving as $CSID => $irow){
            $GQID = '';
            if(isset($ertel_csid[$CSID])){
                $nModif++;
                $GQID = $ertel_csid[$CSID]['GQID'];
                $NUM = str_replace('D10-', '', $GQID);
            }
            $new = $irow;
            $new['GQID'] = $GQID;
            $output .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        $outfile = $infile;
        file_put_contents($outfile, $output);
        $report .= "Modified $nModif lines\n";
        return $report;
    }
    
}// end class