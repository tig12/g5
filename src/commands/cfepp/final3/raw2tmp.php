<?php
/********************************************************************************
    Importation of data/raw/cfepp/final3
    to  data/tmp/cfepp/final3.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-20 18:19:34+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class raw2tmp implements Command {

    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $infile = Final3::rawFilename();
        $outfile = Final3::tmpFilename();
        $outfileRaw = Final3::tmpRawFilename();
        
        $report =  "--- cfepp final3 raw2tmp ---\n";
        
        $rows = Final3::loadRawFile();
exit;
        
        $n = 0;
        $res = [];
        $resRaw = []; // to keep trace of the original values
        for($i=0; $i < count($rows); $i++){
            if($i == 0){
                continue; // line containing field names
            }
            $fields = explode(Final3::RAW_CSV_SEP, $rows[$i]);
            $new = array_fill_keys(Final3::TMP_FIELDS, '');;
            // HERE modify some ids to conform to si42
            $id = $fields[0];
            if(isset(Final3::IRVING_SI42[$id])){
                $new['CSID'] = Final3::IRVING_SI42[$id];
            }
            else{
                $new['CSID'] = $id;
            }
            $new['FNAME'] = $fields[1];
            $new['GNAME'] = $fields[2];
            $day = $fields[5]
                 . '-' . str_pad($fields[4] , 2, '0', STR_PAD_LEFT)
                 . '-' . str_pad($fields[3] , 2, '0', STR_PAD_LEFT);
            $h = $fields[6];
            if($fields[8] == 'P' || $fields[8] == 'P1'){
                // consider P1 because 2 records are bugged (121 and 295, see self::tz())
                $h += 12;
            }
            $h = str_pad($h , 2, '0', STR_PAD_LEFT);
            $min = str_pad($fields[7] , 2, '0', STR_PAD_LEFT);
            $new['DATE'] = "$day $h:$min";
            $new['TZO'] = self::tz($fields[9]);
            $new['LG'] = -self::lgLat($fields[11], $fields[12]);
            $new['LAT'] = self::lgLat($fields[13], $fields[14]);
            $new['C2'] = $fields[10];
            $new['CY'] = 'US';
            $new['SPORT'] = Final3::SPORT_IRVING_G5[$fields[15]];
            $new['MA36'] = $fields[16];
            $new['CANVAS'] = trim($fields[17]);
            $res[$new['CSID']] = $new; // key here only useful for ksort
            $n++;
            // to keep trace of original values
            $newRaw = [
                'Satz#' => $fields[0],
                'NAME' => $fields[1],
                'VORNAME' => $fields[2],
                'GEBDAT' => implode(' ', [$fields[3], $fields[4], $fields[5]]),
                'GEBZEIT' => implode(' ', [$fields[6], $fields[7]]),
                'AMPM' => $fields[8],
                'ZEITZONE' => $fields[9],
                'GEBORT' => $fields[10],
                'LO1' => $fields[11],
                'LO2' => $fields[12],
                'LA1' => $fields[13],
                'LA2' => $fields[14],
                'SPORTART' => $fields[15],
                'MARS' => $fields[16],
                'BATCH' => $fields[17],
            ];
            $resRaw[$new['CSID']] = $newRaw;
        }
        
        ksort($res); // necessary because of inversions generated by Final3::IRVING_SI42
        
        $output = implode(G5::CSV_SEP, Final3::TMP_FIELDS) . "\n";
        foreach($res as $row){
            $output .= implode(G5::CSV_SEP, $row) . "\n";
        }
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $output);
        $report .= "Stored $n records in $outfile\n";
        
        // keep trace of the original values
        $outputRaw = implode(G5::CSV_SEP, Final3::RAW_FIELDS) . "\n";
        foreach(array_keys($res) as $k){
            $outputRaw .= implode(G5::CSV_SEP, $resRaw[$k]) . "\n";
        }
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfileRaw, $outputRaw);
        $report .= "Stored $n records in $outfileRaw\n";
        
        return $report;
    }
    
    /** Auxiliary of execute() **/
    private static function lgLat($deg, $min){
        return $deg + round(($min / 60), 5);
    }
    
    /**
        Auxiliary of execute()
        Computes the timezone offset
    **/
    private static function tz($str){
        if($str == '0,5'){
            // bug for 2 records :
            // 121 Fujii Paul Takashi 1940-07-06
            // 295 Rocha Ephraim 1923-09-18
            return '-10:30';
        }
        // all other records contain integer offsets
        return '-' . str_pad($str , 2, '0', STR_PAD_LEFT) . ':00';
    }
    
} // end class