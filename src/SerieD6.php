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

class SerieD6{
    
        /** Fields in the resulting csv **/
        private static $fieldnames = [
            'NUM',
            'NAME',
            'DATE',
            'PLACE',
            'COU',
            'LG',
            'LAT',
        ];
    
        /** String written to  **/
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
            foreach($lines as $line){
                $fields = explode(Gauquelin5::CSV_SEP, $line);
                if($fields[0] == 'NUM'){
                    $res .= $line . "\n"; // first line
                    continue;
                }
                if($fields[3] == '' && $fields[3] != self::FAILURE_MARK){
                    // PLACE is empty, so call geonames.org
                }
            }
break;
        }
    }
    
    
}// end class    
