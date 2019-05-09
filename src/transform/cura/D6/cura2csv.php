<?php
/********************************************************************************
    Importation of Gauquelin 5th edition ; code specific to serie D10
    matches first list and chronological order list
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\init\Config;
use g5\transform\cura\Cura;

class cura2csv{
    
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
    public static function action($serie){
die("WARNING : D6 import not done because it will erase geonames information.\nFIX THE CODE and start again\n");
        if($serie != 'D6'){
            throw new Exception("SerieD6::raw2csv() - Bad value for parameter \$serie : $serie ; must be 'D6'");
        }
        $report =  "--- Importing serie $serie\n";
        $raw = Cura::readHtmlFile($serie);
        // Fix an error on a latitude in cura file
        $raw = str_replace(
            '356	8	1	1925	11	0	0	36N05	00W56	Ruiz Bernardo',
            '356	8	1	1925	11	0	0	38N05	00W56	Ruiz Bernardo',
            $raw);
        $file_serie = Cura::subject2filename($serie);
        preg_match('#<pre>.*?(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse list in " . $file_serie);
        }
        $nb_stored = 0;
        $csv = '';
        $csv = implode(Config::$data['CSV_SEP'], self::$fieldnames) . "\n";
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = [];
            $new['NUM'] = trim($cur[0]);
            $new['NAME'] = trim($cur[9]);
            $day = Cura::computeDay(['DAY' => $cur[1], 'MON' => $cur[2], 'YEA' => $cur[3]]);
            $hour = Cura::computeHHMM(['H' => $cur[4], 'MN' => $cur[5]]);
            $new['DATE'] = "$day $hour";
            $new['PLACE'] = '';
            $new['COU'] = '';
            $new['GEOID'] = '';
            $new['LG'] = Cura::computeLg($cur[8]);
            $new['LAT'] = Cura::computeLat($cur[7]);
            $csv .= implode(Config::$data['CSV_SEP'], $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
}// end class    
