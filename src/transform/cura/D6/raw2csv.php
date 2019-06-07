<?php
/********************************************************************************
    Importation of cura file D6
    450 New famous European Sports Champions
    
    Matches first list and chronological order list
    
    @license    GPL
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\Names;
use g5\transform\cura\Cura;

class raw2csv implements Command{
    
        /** String written in field PLACE to indicate that a call to geonames webservice failed **/
        const FAILURE_MARK = 'XXX';
    
    // *****************************************
    /** 
        Parses file D6 and stores it in a csv file
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        $datafile = 'D6';
            
        $report =  "--- Importing $datafile ---\n";
        $raw = Cura::readHtmlFile($datafile);
        // Fix an error on a latitude in cura file
        $raw = str_replace(
            '356	8	1	1925	11	0	0	36N05	00W56	Ruiz Bernardo',
            '356	8	1	1925	11	0	0	38N05	00W56	Ruiz Bernardo',
            $raw);
        $file_serie = Cura::rawFilename($datafile);
        preg_match('#<pre>.*?(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse list in " . $file_serie);
        }
        $nb_stored = 0;
        $csv = '';
        $csv = implode(G5::CSV_SEP, D6::FIELDNAMES) . "\n";
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = [];
            $new['NUM'] = trim($cur[0]);
            [$new['FNAME'], $new['GNAME']] = Names::familyGiven(trim($cur[9]));
            $day = Cura::computeDay(['DAY' => $cur[1], 'MON' => $cur[2], 'YEA' => $cur[3]]);
            $hour = Cura::computeHHMM(['H' => $cur[4], 'MN' => $cur[5]]);
            $new['DATE'] = "$day $hour";
            $new['PLACE'] = '';
            $new['CY'] = '';
            $new['GEOID'] = '';
            $new['LG'] = Cura::computeLg($cur[8]);
            $new['LAT'] = Cura::computeLat($cur[7]);
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $nb_stored ++;
        }
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        return $report;
    }
    
    
}// end class    
