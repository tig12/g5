<?php
/********************************************************************************
    Importation of Gauquelin 5th edition ; code specific to series E1 and E3
    
    @todo   information about arrondissement (Paris and Lyon) is not imported => add it to imported data
    @todo   matching to geonames is not complete
    @todo   informations preceeding the name, like L - + *, are not imported
    
    @license    GPL
    @history    2017-05-02 04:32:44+02:00, Thierry Graff : creation
********************************************************************************/
namespace gauquelin5;

use gauquelin5\Gauquelin5;
use gauquelin5\init\Config;

class SerieE1_E3{
    
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
            'AC' => 'AC', // [including Pop Singers]
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
        @param  $serie Must be 'E1' or 'E3'
        @return report
    **/
    public static function raw2csv($serie){
        if($serie != 'E1' && $serie != 'E3'){
            throw new Exception("SerieE1_E3::raw2csv() - Bad value for parameter \$serie : $serie ; must be 'E1' or 'E3'");
        }
        // config - todo : check validity of values put in config
        $report_type = Config::$data['raw2csv']['report'][$serie]; // 'full', 'small', 'tz' or 'geo'
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
        $report .= "Importing $serie\n";
        //
        // parse first list (with birth date and place)
        //
        $res1 = [];
        $raw = Gauquelin5::readHtmlFile($serie);
        preg_match('#<pre>\s*(NUM.*?COD)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception($serie . " - Unable to parse $file - first list");
        }
        $lines = explode("\n", $m[2]);
        // to fix typos : O are replaced by zero ; A by 3 ; S by 5, G by 6 ; B by 8
        $fix_names = ['0'=>'O', '3'=>'A', '5'=>'S', '6'=>'G', '8'=>'B'];
        foreach($lines as $line){
            self::$n_total++;
            $new = [];
            $new['NUM'] = trim(substr($line, 0, 5));
            $new['PRO'] = self::PROFESSIONS[$serie][trim(substr($line, 8, 5))];
            $new['NOTE'] = trim(substr($line, 14, 2)); // L * + -
            $name = trim(substr($line, 17, 30));
            $new['NAME'] = strtr($name, $fix_names);
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
                $report .= 'Geonames not matched for ' . $new['NUM'] . ' ' . $new['NAME'] . ' : ' . $CITY . ' ' . $COD . "\n";
            }
            // compute timezone
            if($lg != ''){
                [$offset, $err] = \TZ_fr::offset($date, $lg, $COD);
                if($err){
                    self::$n_missing_timezone++;
                    if($do_report_tz){
                        $report .=  'TZ not computed for ' . $new['NUM'] . ' ' . $new['NAME'] . ' : ' . $err . "\n";
                    }
                }
                else{
                    $date .= $offset;
                }
            }
            else{
                self::$n_missing_timezone++;
            }
            // Fill res
            $new['DATE'] = $date;
            $new['PLACE'] = $place_name;
            $new['LG'] = $lg;
            $new['LAT'] = $lat;
            $new['COD'] = $adm2;
            $new['COU'] = $country;
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
        }
        //
        // store in destination csv file
        //
        $res1 = \lib::sortByKey($res1, 'NUM');
        $fieldnames = [
            'NUM',
            'PRO',
            'NOTE',
            'NAME',
            'DATE',
            'PLACE',
            'LON',
            'LAT',
            'COD',
            'COU',
            'GEOID',
            'MO',
            'VE',
            'MA',
            'JU',
            'SA',
        ];
        $csv = implode(Gauquelin5::CSV_SEP, $fieldnames) . "\n";
        foreach($res1 as $fields){
            $csv .= implode(Gauquelin5::CSV_SEP, $fields) . "\n";
        }
        $csvfile = Config::$data['dirs']['2-cura-csv'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        return $report;
    }
    
    
    // ******************************************************
    /**
        Computes the geographical informations of a record
        Tries to match geonames.org
        @param  $CITY   Content of column CITY in cura file
        @param  $COD    Content of column COD in cura file = dept for France, adm2 for geonames
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
        $slug = \lib::slugify($name);
        // HERE call to Geonames to match
        $geonames = \Geonames::matchFromSlug([
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
    
    
    // ******************************************************
    /**
        Parses one file E1 or E3 and stores it in a csv file
        The resulting csv file contains informations of the 2 lists
        @param  $serie Must be 'E1' or 'E3'
        @return report
    **/
    public static function generateCorrected($serie){
        if($serie != 'E1' && $serie != 'E3'){
            throw new Exception("SerieE1_E3::raw2csv() - Bad value for parameter \$serie : $serie ; must be 'E1' or 'E3'");
        }
    }
    
}// end class    

