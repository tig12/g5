<?php
/********************************************************************************
    Add missing geographic informations to 5-tmp/cura-csv/D6.csv.
    Uses geonames.org web service.
    
    This code operates on file 5-tmp/geonames/D6.csv
    And then transfers the data in 5-tmp/cura-csv/D6.csv
    This is done to prevent accidental erasure of previous calls to geonames ws : 
        - call cura2csv
        - call cura2geo
        - call cura2csv again => all previous geo information erased
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\init\Config;
use g5\transform\cura\Cura;

class cura2Geo{
    
        /** Fields in the resulting csv **/
        private static $fieldnames = [
            'NUM',
            'NAME',
            'DATE',
            'PLACE',
            'COU',
            'GEOID',
            'LG',
            'LAT',
        ];
    
        /** String written in field PLACE to indicate that a call to geonames webservice failed **/
        const FAILURE_MARK = 'XXX';
    
        
    // ******************************************************
    /**
        Add missing geographic informations to 5-tmp/cura-csv/D6.csv.
    **/
    public static function action($serie){
        if($serie != 'D6'){
            throw new Exception("SerieD6::raw2csv() - Bad value for parameter \$serie : $serie ; must be 'D6'");
        }
        $report =  "--- Computing geographic information for $serie\n";
        $inputFile = Config::$data['dirs']['5-cura-csv'] . DS . $serie . '.csv';
        $outputFile = Config::$data['dirs']['5-geonames'] . DS . $serie . '.csv';
        
        if(!is_file($inputFile)){
        
        }
        
        while(true){
            $res = '';
            // load csv file
            $raw = file_get_contents($inputFile);
            $lines = explode("\n", $raw);
            $newinfo = false; // true if a new geo inf has already been written
            foreach($lines as $line){
                if($newinfo){
                    // copy the rest of the csv file 
                    $res .= $line . "\n";
                    continue;
                }
                $fields = explode(Config::$data['CSV_SEP'], $line);
                if(!isset($fields[3])){
                    break 2; // ===== HERE break enclosing while(true) =====
                }
                if($fields[0] == 'NUM'){
                    // first line
                    $res .= $line . "\n";
                    continue;
                }
                if($fields[3] != ''){
                    // line already completed with geo informations
                    $res .= $line . "\n";
                    continue;
                }
                // here a new line is treated
                // failure or success, a new information goes in the file
                $newinfo = true;
                $lg = $fields[6];
                $lat = $fields[7];
                $geonames = \Geonames::cityFromLgLat(Config::$data['geonames']['username'], $lg, $lat, true);
                if($geonames['error']){                       
                    echo $fields[0] . ' ' . $fields[1] . ' ' . print_r($geonames, true) . "\n";
                    $fields[3] = self::FAILURE_MARK;
                    $res .= implode(Config::$data['CSV_SEP'], $fields) . "\n";
                    continue;
                }
                // here call to Geonames::cityFromLgLat() was sucessful
                echo $fields[0] . ' ' . $fields[1] . " : write geo info\n";
                $dtu = \TZ::offset($fields[3], $geonames['result']['timezone']);
                $fields[2] .= $dtu;
                $fields[3] = $geonames['result']['name'];
                $fields[4] = $geonames['result']['country'];
                $fields[5] = $geonames['result']['geoid'];
                $res .= implode(Config::$data['CSV_SEP'], $fields) . "\n";
                // 0 'NUM', 1 'NAME', 2 'DATE', 3 'PLACE', 4 'COU', 5 'GEOID', 6 'LG', 7 'LAT'
            }
            // Write back the csv 
            file_put_contents($inputFile, $res);
            \lib::dosleep(1.5); // keep cool with geonames.org ws
        } // end whle true
    }
    
}// end class    
