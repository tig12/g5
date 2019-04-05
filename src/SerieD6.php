<?php
/********************************************************************************
    Importation of Gauquelin 5th edition ; code specific to serie D10
    matches first list and chronological order list
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace gauquelin5;

use gauquelin5\Gauquelin5;
use gauquelin5\init\Config;

/* 
Orihuela ES
356 Ruiz Bernardo
Array
(
    [result] => Array
        (
            [name] => Falcon
            [geoid] => 2496813
            [country] => DZ
        )

    [error] => Unable to compute timezone
)
*/


class SerieD6{
    
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
    
    // *****************************************
    /** 
        Parses file D6 and stores it in a csv file
        @param  $serie  String identifying the serie (must be 'D6')
        @return report
        @throws Exception if unable to parse
    **/
    public static function raw2exported($serie){
        if($serie != 'D6'){
            throw new Exception("SerieD6::raw2exported() - Bad value for parameter \$serie : $serie ; must be 'D6'");
        }
        $report =  "--- Importing serie $serie\n";
        $raw = Gauquelin5::readHtmlFile($serie);
        $file_serie = Gauquelin5::serie2filename($serie);
        preg_match('#<pre>.*?(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse list in " . $file_serie);
        }
        $nb_stored = 0;
        $csv = '';
        $csv = implode(Gauquelin5::CSV_SEP, self::$fieldnames) . "\n";
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = [];
            $new['NUM'] = trim($cur[0]);
            $new['NAME'] = trim($cur[9]);
            $day = Gauquelin5::computeDay(['DAY' => $cur[1], 'MON' => $cur[2], 'YEA' => $cur[3]]);
            $hour = Gauquelin5::computeHHMM(['H' => $cur[4], 'MN' => $cur[5]]);
            $new['DATE'] = "$day $hour";
            $new['PLACE'] = '';
            $new['COU'] = '';
            $new['GEOID'] = '';
            $new['LG'] = Gauquelin5::computeLg($cur[8]);
            $new['LAT'] = Gauquelin5::computeLat($cur[7]);
            $csv .= implode(Gauquelin5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dirs']['2-cura-exported'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /** Add missing geographic informations to D6.csv **/
    public static function computeGeo($serie){
        if($serie != 'D6'){
            throw new Exception("SerieD6::raw2exported() - Bad value for parameter \$serie : $serie ; must be 'D6'");
        }
        $report =  "--- Computing geographic information for $serie\n";
        $csvfile = Config::$data['dirs']['2-cura-exported'] . DS . $serie . '.csv';
        
        while(true){
            $res = '';
            // load csv file
            $raw = file_get_contents($csvfile);
            $lines = explode("\n", $raw);
            $newinfo = false; // true if a new geo inf has already been written
            foreach($lines as $line){
                if($newinfo){
                    // copy the rest of the csv file 
                    $res .= $line . "\n";
                    continue;
                }
                $fields = explode(Gauquelin5::CSV_SEP, $line);
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
                    $res .= implode(Gauquelin5::CSV_SEP, $fields) . "\n";
                    continue;
                }
                // here call to Geonames::cityFromLgLat() was sucessful
                echo $fields[0] . ' ' . $fields[1] . " : write geo info\n";
                $dtu = \TZ::offset($fields[3], $geonames['result']['timezone']);
                $fields[2] .= $dtu;
                $fields[3] = $geonames['result']['name'];
                $fields[4] = $geonames['result']['country'];
                $fields[5] = $geonames['result']['geoid'];
                $res .= implode(Gauquelin5::CSV_SEP, $fields) . "\n";
                // 0 'NUM', 1 'NAME', 2 'DATE', 3 'PLACE', 4 'COU', 5 'GEOID', 6 'LG', 7 'LAT'
            }
            // Write back the csv 
            file_put_contents($csvfile, $res);
            dosleep(1.5); // keep cool with geonames.org ws
        } // end whle true
    }
    
    
}// end class    

// ******************************************************
/** 
    like sleep() but parameter is a nb of seconds, and it prints a message
**/
function dosleep($x){
    echo "dosleep($x) ";
    usleep($x * 1000000);
    echo " - end sleep\n";
}
