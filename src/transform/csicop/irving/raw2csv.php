<?php
/********************************************************************************
    Importation of 1-irving/rawlins-ertel-data.csv
    to  5-csicop/408-csicop-irving.csv
    
    @license    GPL
    @history    2019-12-23 00:38:49+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\irving;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;

class raw2csv implements Command{
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Irving::raw_filename();
        $outfile = Irving::tmp_filename();
        
        $report =  "--- Importing file $infile ---\n";
        
        $rows = file($infile);
        
        $res = implode(G5::CSV_SEP, Irving::TMP_FIELDS) . "\n";
        $n = 0;
        for($i=0; $i < count($rows); $i++){
            if($i == 0){
                continue; // field names
            }
            $fields = explode(Irving::RAW_CSV_SEP, $rows[$i]);
            $new = [];
            $new['CSID'] = $fields[0];
            $new['FNAME'] = $fields[1];
            $new['GNAME'] = $fields[2];
            $day = $fields[5]
                 . '-' . str_pad($fields[4] , 2, '0', STR_PAD_LEFT)
                 . '-' . str_pad($fields[3] , 2, '0', STR_PAD_LEFT);
            $h = $fields[6];
            if($fields[8] == 'P'){
                $h += 12;
            }
            $h = str_pad($h , 2, '0', STR_PAD_LEFT);
            $min = str_pad($fields[7] , 2, '0', STR_PAD_LEFT);
            $new['DATE'] = "$day $h:$min";
            $new['TZ'] = $fields[9];
            $new['C2'] = $fields[10];
            $new['LG'] = -self::lgLat($fields[11], $fields[12]);
            $new['LAT'] = self::lgLat($fields[13], $fields[14]);
            $new['SPORT'] = $fields[15];
            $new['MA36'] = $fields[16];
            $new['CANVAS'] = trim($fields[17]);
            $n++;
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        file_put_contents($outfile, $res);
        $report .= "Generated $outfile - $n records stored\n";
        return $report;
    }
    
    // ******************************************************
    /**
        @param $
    **/
    private static function lgLat($deg, $min){
        return $deg + round(($min / 60), 5);
    }
    
}// end class