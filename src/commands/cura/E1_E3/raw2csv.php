<?php
/********************************************************************************
    Importation of cura files E1 and E3
    E1 : 2154 New French Physicians, Army Leaders, Top Executives
    E3 : 1540 New French Writers, Artists, Actors, Politicians & Journalists
    
    NOTE : Leading zeroes are removed from NUM in the resulting csv file.
    This is done at the end of the function - all computations are done using original NUMs (with leading zeroes).

    @todo   information about arrondissement (Paris and Lyon) is not imported
    
    @license    GPL
    @history    2017-05-02 04:32:44+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\E1_E3;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\Names;
use g5\model\Geonames;
use g5\commands\cura\Cura;
use tiglib\arrays\sortByKey;
use tiglib\strings\slugify;
use tiglib\timezone\offset_fr;
use tiglib\geonames\database\matchFromSlug;

class raw2csv implements Command{
    
    private static $n_missing_places = 0;
    private static $n_missing_timezone = 0;
    private static $n_total = 0;
    /**
        Associations between profession codes and profession names for the files of E1 and E3
    **/
    const PROFESSIONS = [
        'E1' => [
            'PH' => 'PH',
            'MI' => 'MI',
            'EX' => 'EX',
            'PH,EX' => 'PH+EX',
            'MI,PH' => 'MI+PH',
            'MI,EX' => 'MI+EX',
        ],
        'E3' => [
            'PO' => 'PO',
            'JO' => 'JO',
            'WR' => 'WR',
            'AC' => 'AC',   // [including Pop Singers]
            'PAI' => 'PAI', // [including 1 sculptor]
            'MUS' => 'MUS',
            'OPE' => 'OPE',
            'CAR' => 'CAR',
            'DAN' => 'DAN',
            'PHO' => 'PHO',
        ],
    ];
    

    // ******************************************************
    /** 
        Parses one file E1 or E3 and stores it in a csv file
        The resulting csv file contains informations of the 2 lists
        @param  $params Array containing 3 elements :
                        - a string identifying what is processed (ex : 'E1')
                        - "raw2csv" (useless here)
                        - The report type. Can be "small", "full", "tz" or "geo"
        @return report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 3){
            return "INVALID PARAMETER : " . $params[3] . " - raw2csv doesn't need this parameter\n";
        }
        $msg = "raw2csv needs a parameter to specify which output it displays. Can be :\n"
             . "  small : echoes only global report\n"
             . "  tz : echoes the records for which timezone information is missing\n"
             . "  geo : echoes the records for which geonames matching couldn't be done\n"
             . "  full : equivalent to tz and geo\n";
        if(count($params) < 3){
            return "MISSING PARAMETER : $msg";
        }
        if(!in_array($params[2], ['small', 'full', 'tz', 'geo'])){
            return "INVALID PARAMETER : $msg";
        }
        $report_type = $params[2];
        $datafile = $params[0];

        $do_report_geo = $do_report_tz = false;
        if($report_type == 'full' || $report_type == 'geo'){
            $do_report_geo = true;
        }
        if($report_type == 'full' || $report_type == 'tz'){
            $do_report_tz = true;
        }
        self::$n_missing_places = 0;
        self::$n_missing_timezone = 0;
        self::$n_total = 0;
        $report = '';
        $report .= "Importing $datafile\n";
        //
        // parse first list (with birth date and place)
        //
        $res1 = [];
        $raw = Cura::loadRawFile($datafile);
        preg_match('#<pre>\s*(NUM.*?COD)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception($datafile . " - Unable to parse $file - first list");
        }
        $lines = explode("\n", $m[2]);
        // to fix typos : in the html, O are replaced by zero ; A by 3 ; S by 5, G by 6 ; B by 8
        $fix_names = ['0'=>'O', '3'=>'A', '5'=>'S', '6'=>'G', '8'=>'B'];
        foreach($lines as $line){
            self::$n_total++;
            $new = [];
            $new['NUM'] = trim(substr($line, 0, 5));
            $new['OCCU'] = self::PROFESSIONS[$datafile][trim(substr($line, 8, 5))];
            $new['NOTE'] = trim(substr($line, 14, 2)); // L * + -
            $name = trim(substr($line, 17, 30));
            [$new['FNAME'], $new['GNAME']] = Names::familyGiven(strtr($name, $fix_names));
            $y = trim(substr($line, 61, 6));
            $m = trim(substr($line, 55, 4));
            $d = trim(substr($line, 49, 4));
            $h = trim(substr($line, 69, 9));
            $date = "$y-$m-$d $h";
            $CITY = trim(substr($line, 78, 25));
            $COD = trim(substr($line, 104));
            // match place to geonames
            [$country, $adm2, $place_name, $geoid, $lg, $lat] = self::compute_geo($CITY, $COD, $date);
            if($lg == '' && $do_report_geo){
                $report .= 'Geonames not matched for ' . $new['NUM'] . ' ' . $new['FNAME'] . ' ' . $new['GNAME'] . ' : ' . $CITY . ' ' . $COD . "\n";
            }
            // compute timezone
            if($lg != ''){
                [$offset, $err] = offset_fr::compute($date, $lg, $COD);
                if($err){
                    self::$n_missing_timezone++;
                    if($do_report_tz){
                        $report .=  'TZ not computed for ' . $new['NUM'] . ' ' . $new['FNAME'] . ' ' . $new['GNAME'] . ' : ' . $err . "\n";
                    }
                }
                else{
                    $date .= $offset;
                }
            }
            else{
                self::$n_missing_timezone++;
            }
            // fill res
            $new['DATE'] = $date;
            $new['PLACE'] = $place_name;
            $new['LG'] = $lg;
            $new['LAT'] = $lat;
            $new['C2'] = $adm2;
            $new['CY'] = $country;
            $new['GEOID'] = $geoid;
            $res1[$new['NUM']] = $new;
        }
        $report .= self::$n_total  . " lines parsed\n";
        $report .= self::$n_missing_places . " places not matched\n";
        $report .= self::$n_missing_timezone . " timezone offsets not computed\n";
        $remain = self::$n_total - self::$n_missing_places - self::$n_missing_timezone;
        $percent = round($remain * 100 / self::$n_total, 2);
        $report .= "$remain persons stored precisely ($percent %)\n";
        //
        // parse the second list (with sectors)
        //
        $res2 = [];
        preg_match('#<div id="contenu"><pre>\s*(NUM.*?NAME)\s*(.*?)\s*</pre>.*?<div id="contenu2"><pre>\s*(NUM.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 5){
            throw new \Exception($file_info . " - Unable to parse second list");
        }
        $raw = $m[2] . "\n" . $m[4];
        $lines = explode("\n", $raw);
        foreach($lines as $line){
            $current = [];
            $tmp = preg_split('/\s+/', $line); // both explode("\t") and explode(' ') don't work for all lines
            $num = $tmp[0];
            if($num == '0811'){ // fix a typo in 902gdE3.html
                $line = str_replace('2104', '21 04', $line);
                $tmp = preg_split('/\s+/', $line);
            }
            if(strlen($tmp[1]) != 2 || strlen($tmp[2]) != 2 || strlen($tmp[3]) != 2 || strlen($tmp[4]) != 2 || strlen($tmp[5]) != 2){
                if($num != '0517'){ // bug in the page for 517
                    echo "\n<pre>"; print_r($tmp); echo "</pre>";
                    throw new \Exception($file_info . " - list 2 - line not parsed <br/>$line");
                }
            }
            $current['MO'] = $tmp[1];
            $current['VE'] = $tmp[2];
            $current['MA'] = $tmp[3];
            $current['JU'] = $tmp[4];
            $current['SA'] = $tmp[5];
            $current['name'] = implode(' ', array_slice($tmp, 6));
            $res2[$num] = $current;
        }
        //
        // merge the 2 lists
        //
        if(count($res1) != count($res2)){
            throw new \Exception('The 2 lists are different : count($res1) = ' . count($res1) . ' ; count($res2) = ' . count($res2));
        }
        foreach($res1 as $num1 => $fields1){
            if(!isset($res2[$num1])){
                throw new \Exception("<br/>missing $num1 in \$res2");
            }
            // complete $res1 with sector names
            $res1[$num1]['MO'] = $res2[$num1]['MO'];
            $res1[$num1]['VE'] = $res2[$num1]['VE'];
            $res1[$num1]['MA'] = $res2[$num1]['MA'];
            $res1[$num1]['JU'] = $res2[$num1]['JU'];
            $res1[$num1]['SA'] = $res2[$num1]['SA'];
            $res1[$num1]['NOTES'] = '';
        }
        //                                          
        // store in destination csv file
        //
        $res1 = sortByKey::compute($res1, 'NUM');
        $csv = implode(G5::CSV_SEP, E1_E3::FIELDNAMES) . "\n";
        foreach($res1 as $fields){
            // HERE modify NUM
            $fields['NUM'] = ltrim($fields['NUM'], 0);
            $csv .= implode(G5::CSV_SEP, $fields) . "\n";
        }
        $csvfile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        file_put_contents($csvfile, $csv);
        return $report;
    }
    
    
    // ******************************************************
    /**
        Computes the geographical informations of a record
        Tries to match geonames.org
        @param  $CITY   Content of column CITY in cura file
        @param  $COD    Content of column COD in cura file
                        = dept for France
                        = adm2 for geonames
                        = C2 in 5-tmp/ files.
        @return Array containing 6 geographical information :
                    country
                    adm2
                    place name
                    geoid
                    lg
                    lat
                $geoid, $lg, $lat contain empty string if they can't be computed
    **/
    private static function compute_geo($CITY, $COD){
        // Not France - manual matching
        switch($COD){
            case 'ALG' : 
                switch($CITY){
                    case 'Alger - Algérie' : return ['DZ', '', 'Alger', '2507480', '3.08746', '36.73225']; break;
                    case 'Blida - Algérie' : return ['DZ', '', 'Blida', '2503769', '2.8277', '36.47004']; break;
                    case 'Constantine - Algérie' : return ['DZ', '', 'Constantine', '2501152', '6.61472', '36.365']; break;
                    case 'Oran - Algérie' : return ['DZ', '', 'Oran', '2485926', '-0.63588', '35.69906']; break;
                }
            break;
            case 'B' : 
                switch($CITY){
                    case 'Anvers - Belgique' : return ['BE', '', 'Antwerpen', '2803138', '4.40346', '51.21989']; break;
                    case 'Bruxelles - Belgique' : return ['BE', '', 'Bruxelles', '2800866', '4.34878', '50.85045']; break;
                    case 'Laeken-Bruxelles - Belg' : return ['BE', '', 'Laeken', '2793656', '4.34844', '50.87585']; break;
                    case 'Mouscron - Belgique' : return ['BE', '', 'Mouscron', '2790595', '3.20639', '50.74497']; break;
                }
            break;
            case 'GER' : 
                return ['DE', '', 'Koblenz', '2886946', '7.57884', '50.35357'];
            break;                                               
            case 'LUX' : 
                return ['LU', '', 'Luxembourg', '2960316', '6.13', '49.61167'];
            break;
            case 'MAR' : 
                return ['MA', '061', 'Meknès', '2542715', '-5.54727', '33.89352'];
            break;
        }
        // France - $COD is département
        $name = $CITY;
        $adm2 = $COD;
        // Adapt name for common cases
        $name = preg_replace('/(.*?)\b[Ss]t\b(.*?)/', '$1saint$2', $name);
        $name = preg_replace('/(.*?) \d+ème/', '$1', $name); // loss of information "arrondissement"
        $name = str_replace(' 1er', '', $name); // loss of information "arrondissement"
        $name = str_replace('/', ' sur ', $name);
        // particular cases handed manually
        // => convert names as spelled in files E1 and E3 to the corresponding names in geonames
        // Adapt département when it has changed since Gauquelin epoch
        if($name == 'Asnières'){
            $name = 'Asnières-sur-Seine';
            $adm2 = '92';
        }
        else if($name == 'Gaillac-sur-Tarn'){
            $name = 'Gaillac';
        }
        else if($name == 'Chalons sur Marne'){
            $name = 'Châlons-en-Champagne';
        }
        else if($name == 'Vielmur' && $adm2 == '81'){
            $name = 'Vielmur-sur-Agout';
        }
        else if($name == 'saint Maur' && $adm2 == '94'){
            $name = 'Saint-Maur-des-Fossés';
        }
        else if($name == 'Soisy sous Montmoren' && $adm2 == '78'){
            $name = 'Soisy-sous-Montmorency';
            $adm2 = '95';
        }
        else if($name == 'Romans' && $adm2 == '26'){
            $name = 'Romans-sur-Isère';
        }
/* 
        else if($name == '' && $adm2 == ''){
            $name = '';
        }
*/
        $slug = slugify::compute($name);
        // HERE call to Geonames to match
        $pdo = Geonames::compute_dblink();
        $geonames = matchFromSlug::compute($pdo, [
            'slug' => $slug,
            'countries' => ['FR'],
            'admin2-code' => $adm2,
        ]);
        if($geonames){
            return [
                'FR', 
                $adm2,
                $geonames[0]['name'],
                $geonames[0]['geoid'],
                $geonames[0]['longitude'],
                $geonames[0]['latitude'],
            ];
        }
        // not matched
        self::$n_missing_places++;
        return [
                'FR', 
                $adm2,
                $name,
                '',
                '',
                '',
        ];
    }

}// end class    
