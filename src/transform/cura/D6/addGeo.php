<?php
/********************************************************************************
    Add missing geographic informations to 5-tmp/cura-csv/D6.csv.
    Uses geonames.org web service (ws).
    
    This code operates on file   5-tmp/geonames/D6.csv
    And then copies this file to 5-tmp/cura-csv/D6.csv
    This is done to prevent accidental erasure of previous calls to geonames ws : 
        - call raw2csv
        - call addGeo
        - call raw2csv again => all previous geo information erased
    
    This code can be interrupted in the middle of execution and be called several times.
    Previous calls to geonames.org ws are stored in 5-tmp/geonames/D6.csv.
    So a new call will use these results and not repeat previous calls to geonames.org ws.
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\Cura;

class addGeo implements Command{
    
        /** String written in field PLACE to indicate that a call to geonames webservice failed **/
        const FAILURE_MARK = 'XXX';
    
        
    // ******************************************************
    /**
        Add missing geographic informations to 5-tmp/geonames/D6.csv and 5-tmp/cura-csv/D6.csv.
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - addGeo doesn't need this parameter\n";
        }
        
        $subject = 'D6';
        $report =  "--- Computing geographic information for $subject ---\n";
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $subject . '.csv';
        $geofile = Config::$data['dirs']['5-geonames'] . DS . $subject . '.csv';
        
        if(!is_file($csvfile)){
            $report .= "Missing file $csvfile\n";
            $report .= "You must run first : php run.php raw2csv D6\n";
        }
        
        if(!is_file($geofile)){
            copy($csvfile, $geofile); // at first exec only
        }
        
        while(true){
            $res = '';
            // load csv file
            $raw = file_get_contents($geofile);
            $lines = explode("\n", $raw);
            $newinfo = false; // true if a new geo info has been written in previous iteration
            foreach($lines as $line){
                if($newinfo){
                    // copy the rest of the csv file 
                    $res .= $line . "\n";
                    continue;
                }
                $fields = explode(G5::CSV_SEP, $line);
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
                    $report .= $fields[0] . ' ' . $fields[1] . ' ' . print_r($geonames, true) . "\n";
                    $fields[3] = self::FAILURE_MARK;
                    $res .= implode(G5::CSV_SEP, $fields) . "\n";
                    continue;
                }
                // here call to Geonames::cityFromLgLat() was sucessful
                $report .= $fields[0] . ' ' . $fields[1] . " : write geo info\n";
                $dtu = \TZ::offset($fields[3], $geonames['result']['timezone']);
                $fields[2] .= $dtu;
                $fields[3] = $geonames['result']['name'];
                $fields[4] = $geonames['result']['country'];
                $fields[5] = $geonames['result']['geoid'];
                $res .= implode(G5::CSV_SEP, $fields) . "\n";
                // 0 'NUM', 1 'NAME', 2 'DATE', 3 'PLACE', 4 'CY', 5 'GEOID', 6 'LG', 7 'LAT'
            }
            // Write back the csv 
            file_put_contents($geofile, $res); // file in 5-tmp/geonames
            copy($geofile, $csvfile); // copy results back in 5-tmp/cura-csv/
            \lib::dosleep(1.5); // keep cool with geonames.org ws
        } // end while true
        copy($geofile, $csvfile); // useful if current execution retrieves 0 information
        $report .=  "Geographic information computed\n";
        return $report;
    }
    
}// end class    
