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

class SerieD10{
    
    /** ISO 3166 code (all data share the same country) **/
    const COUNTRY = 'US';
    
    /**
        Associations between profession codes and profession names
    **/
    const PROFESSIONS = [
        'SP' => 'Sport Champion',
        'MI' => 'Military Man',
        'AC' => 'Actor',
        'PO' => 'Politician',
        'EX' => 'Executive',
        'WR' => 'Writer',
        'SC' => 'Scientist',
        'AR' => 'Artist',
        'X'  => 'Various', 
    ];
    
    // *****************************************
    /** 
        Parses file D10 and stores it in a csv file
        @param  $serie  String identifying the serie (must be 'D10')
        @return report
        @throws Exception if unable to parse
    **/
    public static function import($serie){
        $report =  "--- Importing serie $serie\n";
        $raw = Gauquelin5::readHtmlFile($serie);
        $file_serie = Gauquelin5::serie2filename($serie);
        preg_match('#<pre>\s*(NUM.*?CICO)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse first list (without names) in " . $file_serie);
        }
        $fieldnames = preg_split('/\s+/', $m[1]);
        $nb_stored = 0;
        $csv = '';
        // fields in the resulting csv
        $fieldnames = [
            'NUM',
            'NAME',
            'DATE',
            'PLACE',
            'COU',
            'COD',
            'LON',
            'LAT',
            'PRO',
        ];
        $csv = implode(Gauquelin5::CSV_SEP, $fieldnames) . "\n";
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
// not managed, waiting for a fix in the original file
if(!isset($cur[10])){
    //echo "$line\n";
    continue;
}
if(trim($cur[10]) == ''){
    //echo "$line\n";
    continue;
}
if($cur[6] == '-----'){
    continue;
}
            $new = [];
            $new['NUM'] = trim($cur[0]);
            $new['NAME'] = trim($cur[1]);
            // date time
            $day = Gauquelin5::computeDay(['DAY' => $cur[3], 'MON' => $cur[4], 'YEA' => $cur[5]]);
            $hour = $cur[6];
            // timezone
            $tmp = explode('h', trim($cur[7]));
            $h =  str_pad($tmp[0] , 2, '0', STR_PAD_LEFT);
            $m =  str_pad ($tmp[1] , 2, '0');
            $timezone = '-' . $h . ':' . $m;
            $new['DATE'] = "$day $hour$timezone";
            // place
            $tmp = explode(',', $cur[10]);
            $new['PLACE'] = trim($tmp[0]);
            $new['COU'] = self::COUNTRY;
            $new['COD'] = trim($tmp[1]);
            $new['LON'] = Gauquelin5::computeLg($cur[9]);
            $new['LAT'] = Gauquelin5::computeLat($cur[8]);
            // @todo link to geonames
            $new['PRO'] = self::compute_profession($cur[2]);
            $csv .= implode(Gauquelin5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dest-dir'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /** 
        Compute profession labels(s) from profession code(s)
        Auxiliary of import()
    **/
    private static function compute_profession($pro){
        $codes = explode(',', trim($pro));
        $labels = [];
        foreach($codes as $code){
            $labels[] = self::PROFESSIONS[$code];
        }
        return implode(' + ', $labels);
    }
    
    
}// end class    

