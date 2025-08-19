<?php
/********************************************************************************
    Importation of cura file D6
    450 New famous European Sports Champions
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\D6;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\Names;
use g5\model\Names_fr;
use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura5;

class raw2tmp implements Command {
    
    // *****************************************
    /** 
        Parses file D6 and stores it in a csv file
        @param $params Useless here (consumed by GauqCommand)
                       Contains 2 elements : 'D6' and 'raw2tmp'
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - raw2tmp doesn't need this parameter\n";
        }
        
        $datafile = 'D6';
        
        $report =  "--- gauq $datafile raw2tmp ---\n";
        $raw = LERRCP::loadRawFile($datafile);
        
        // To fix an error on a latitude in cura file, in line
        // 356	8	1	1925	11	0	0	36N05	00W56	Ruiz Bernardo
        $lat356 = '38N05';
        
        $file_serie = LERRCP::rawFilename($datafile);
        preg_match('#<pre>.*?(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse list in " . $file_serie);
        }
        
        $csv = implode(G5::CSV_SEP, D6::TMP_FIELDS) . "\n";
        $emptyNew = array_fill_keys(D6::TMP_FIELDS, '');
        
        $csvRaw = implode(G5::CSV_SEP, D6::RAW_FIELDS) . "\n";
        $emptyNewRaw = array_fill_keys(D6::RAW_FIELDS, '');
        
        $nb_stored = 0;
        $lines = explode("\n", $m[2]);
        foreach($lines as $line){
            $cur = preg_split('/\t+/', $line);
            $new = $emptyNew;
            $new['NUM'] = trim($cur[0]);
            //
            [$new['FNAME'], $new['GNAME']] = Names::familyGiven(trim($cur[9]));
            if($new['GNAME'] == ''){
                [$new['FNAME'], $new['GNAME']] = Names_fr::fixJean($new['FNAME']);
            }
            if($new['GNAME'] == '' && isset(D6::NAMES_CORRECTIONS[$new['NUM']])){
                [$new['FNAME'], $new['GNAME']] = D6::NAMES_CORRECTIONS[$new['NUM']];
            }
            //
            $new['OCCU'] = 'sportsperson';
            //
            $day = Cura5::computeDay(['DAY' => $cur[1], 'MON' => $cur[2], 'YEA' => $cur[3]]);
            $hour = Cura5::computeHHMM(['H' => $cur[4], 'MN' => $cur[5]]);
            $new['DATE'] = "$day $hour";
            $new['PLACE'] = '';
            $new['CY'] = '';
            $new['C2'] = '';
            $new['GEOID'] = '';
            $new['LG'] = Cura5::computeLg($cur[8]);
            $lat = $new['NUM'] != '356' ? $cur[7] : $lat356;
            $new['LAT'] = Cura5::computeLat($lat);
            // $raw, to keep an exact trace of original values
            $raw = $emptyNewRaw;
            $raw['NUM'] = $cur[0];
            $raw['DAY'] = $cur[1];    
            $raw['MON'] = $cur[2];
            $raw['YEA'] = $cur[3];
            $raw['H'] = $cur[4];
            $raw['MN'] = $cur[5];
            $raw['SEC'] = $cur[6];
            $raw['LAT'] = $cur[7];
            $raw['LON'] = $cur[8];
            $raw['NAME'] = trim($cur[9]);
            //
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $csvRaw .= implode(G5::CSV_SEP, $raw) . "\n";
            $nb_stored ++;
        }
        $csvfile = LERRCP::tmpFilename($datafile);
        file_put_contents($csvfile, $csv);
        $report .= $nb_stored . " lines stored in $csvfile\n";
        //
        $csvRawfile = LERRCP::tmpRawFilename($datafile);
        file_put_contents($csvRawfile, $csvRaw);
        $report .= $nb_stored . " lines stored in $csvRawfile\n";
        return $report;
    }
    
} // end class    
