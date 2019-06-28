<?php
/********************************************************************************
    Add missing geographic informations to 5-cura-csv/D6.csv.
    Uses geonames.org web service.
    
    This code operates on file 5-geonames/D6.csv
    And then copies info from this file to 5-cura-csv/D6.csv
    This is done to prevent accidental erasure of previous calls to geonames web service : 
        - call raw2csv
        - call addGeo
        - call raw2csv again => all previous geo information erased
    
    This code can be interrupted in the middle of execution and be called several times.
    Previous calls to geonames.org web service are stored in 5-tmp/geonames/D6.csv.
    So a new call will use these results and not repeat previous calls to geonames.org web service.
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\Cura;
use tiglib\misc\dosleep;
use tiglib\arrays\csvAssociative;
use tiglib\geonames\webservice\cityFromLgLat;
use tiglib\timezone\offset;

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
        
        $datafile = 'D6';
        $report =  "--- Computing geographic information for $datafile ---\n";
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        $geofile = Config::$data['dirs']['5-geonames'] . DS . $datafile . '.csv';
        
        if(!is_file($csvfile)){
            $report .= "Missing file $csvfile\n";
            $report .= "You must run first : php run-g5.php cura D6 raw2csv\n";
        }
        
        if(!is_file($geofile)){
            copy($csvfile, $geofile); // at first exec only
        }
        
        while(true){
            $res_geo = '';
            // load csv file in 5-tmp/geonames
            $raw_geo = file_get_contents($geofile);
            $lines_geo = explode("\n", $raw_geo);
            $newinfo = false; // true if a new geo info has been written in previous iteration
            foreach($lines_geo as $line_geo){
                if($newinfo){
                    // copy the rest of the csv file 
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                $fields = explode(G5::CSV_SEP, $line_geo);
                if(!isset($fields[3])){
                    break 2; // ===== HERE break enclosing while(true) =====
                }
                if($fields[0] == 'NUM'){
                    // first line
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                if($fields[3] != ''){
                    // line already completed with geo informations
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                // here a new line is treated
                // failure or success, a new information goes in the file
                $newinfo = true;
                $lg = $fields[6];
                $lat = $fields[7];
                // HERE call to geonames web service
                $geonames = cityFromLgLat::compute(Config::$data['geonames']['username'], $lg, $lat, true);
                if($geonames['error']){                       
                    $report .= $fields[0] . ' ' . $fields[1] . ' ' . print_r($geonames, true) . "\n";
                    $fields[3] = self::FAILURE_MARK;
                    $res_geo .= implode(G5::CSV_SEP, $fields) . "\n";
                    continue;
                }
                // here call to geonames web service was sucessful
                $report .= $fields[0] . ' ' . $fields[1] . " : write geo info\n";
                $dtu = offset::compute($fields[3], $geonames['result']['timezone']);
                $fields[2] .= $dtu;
                $fields[3] = $geonames['result']['name'];
                $fields[4] = $geonames['result']['country'];
                $fields[5] = $geonames['result']['geoid'];
                $res_geo .= implode(G5::CSV_SEP, $fields) . "\n";
                // 0 'NUM', 1 'NAME', 2 'DATE', 3 'PLACE', 4 'CY', 5 'GEOID', 6 'LG', 7 'LAT'
            }
            // Write back the csv 
            file_put_contents($geofile, $res_geo); // file in 5-tmp/geonames
            self::geo2csv($geofile, $csvfile); // copy results back in 5-tmp/cura-csv/
            dosleep::execute(1.5); // keep cool with geonames.org web service
        } // end while true
        
        // new execution of geo2csv is necessary
        // if current execution retrieves 0 information from geonames web service
        self::geo2csv($geofile, $csvfile);
        $report .=  "Geographic information computed\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Transfers geo informations from 5-tmp/geonames/D6.csv to 5-tmp/cura-csv/D6.csv
        Replaces copy($geofile, $csvfile) to transfer only relevant columns.
        This function became necessary when modifs in raw2csv rendered 5-tmp/geonames/D6.csv column names obsolete
        and not compatible with new version of 5-tmp/cura-csv/D6.csv
        
    **/
    private static function geo2csv($geofile, $csvfile){
        $geo = csvAssociative::compute($geofile);
        $csv = csvAssociative::compute($csvfile);
        // $geonum = $geo, but keys are NUM
        $geonum = [];
        foreach($geo as $record){
            $geonum[$record['NUM']] = $record;
        }
        
        $res = implode(G5::CSV_SEP, array_keys($csv[0])) . "\n";
        
        foreach($csv as $record){
            $record['CY'] = $geonum[$record['NUM']]['CY']; // HERE transfer information
            $res .= implode(G5::CSV_SEP, $record) . "\n";
        }
        file_put_contents($csvfile, $res);
    }
    
}// end class    
