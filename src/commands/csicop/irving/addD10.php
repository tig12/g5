<?php
/********************************************************************************
    Add fields GQID and PLACE to  5-csicop/408-csicop-irving.csv
    
    @license    GPL
    @history    2019-12-24 09:51:10+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\commands\newalch\ertel4391\Ertel4391;

class addD10 implements Command{

    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Irving::tmp_filename();
        
        $report =  "--- Add column GQID and PLACE to $infile ---\n";
        
        // Add a key GQID between CSID and FNAME
        // code works if $infile has fieds described in Irving::TMP_FIELDS
        // (this is the case after raw2csv)
        
        $keys = [];
        foreach(Irving::TMP_FIELDS as $k){
            $keys[] = $k;
            if($k == 'CSID'){
                $keys[] = 'GQID';
            }
            if($k == 'TZ'){
                $keys[] = 'PLACE';
            }
        }
        
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
        
        $output = implode(G5::CSV_SEP, $keys) . "\n";
        
        $nModif = 0;
        foreach($irving as $CSID => $irow){
            $GQID = $PLACE = '';
            if(isset($ertel_csid[$CSID])){
                $nModif++;
                $GQID = $ertel_csid[$CSID]['GNUM'];
                $NUM = str_replace('D10-', '', $GQID);
                $PLACE = $d10[$NUM]['PLACE'];
            }
            $new = array_fill_keys($keys, '');
            foreach($irow as $k => $v){
                $new[$k] = $v;
            }
            $new['GQID'] = $GQID;
            $new['PLACE'] = $PLACE;
            $output .= implode(G5::CSV_SEP, $new) . "\n";
        }
        
        $outfile = $infile;
        file_put_contents($outfile, $output);
        $report .= "Modified $nModif lines\n";
        return $report;
    }
    
}// end class