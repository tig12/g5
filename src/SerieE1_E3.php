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
        @param  $serie Muste be 'E1' or 'E3'
        @return report
    **/
    public static function import($serie){
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
            $place_name = trim(substr($line, 78, 25));
            $dept = trim(substr($line, 104));
            // files E1 and E3 contain only births in France and Luxembourg
            if($dept == 'LUX'){
                $place_name = 'Luxembourg';
                $country = 'LU';
                $dept = '';
            }
            else{
                $country = 'FR';
            }
            // match place to geonames
            if($country == 'LUX'){
                [$geoid, $lg, $lat] = ['2960316', '6.13', '49.61167'];
            }
            else{
                [$geoid, $tmp_place_name, $lg, $lat] = self::matchFrenchPlace($place_name, $dept);
                if($tmp_place_name != ''){
                    $place_name = $tmp_place_name; // replace original name by geoid name (in general better spelled)
                }
            }
            if($lg){ // if longitude is known
                list($offset, $err) = \FrenchTimezone::offset_fr($date, $lg, $dept);
                if($err){
//                    $report .= "$err\n";
                    self::$n_missing_timezone++;
                }
                else{
                    $date .= $offset;
                }
            }
            $new['DATE'] = $date;
            $new['PLACE'] = $place_name;
            $new['LON'] = $lg;
            $new['LAT'] = $lat;
            $new['COD'] = $dept;
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
        $csvfile = Config::$data['dest-dir'] . DS . $serie . '.csv';
        file_put_contents($csvfile, $csv);
        return $report;
    }
    
    
    // ******************************************************
    /**
        Auxiliary function of import()
        @param  $name string Name of a place
        @return array of 3 elements :
                    geoid (= geonames.org id)
                    name
                    lg
                    lat
                Or an array containing 4 empty strings for elements that could not be computed
    **/
    public static function matchFrenchPlace($name, $dept){
        $name = preg_replace('/(.*?)\b[Ss]t\b(.*?)/', '$1saint$2', $name);
        // loss of information "arrondissement"
        $name = preg_replace('/(.*?) \d+ème/', '$1', $name);
        $name = str_replace(' 1er', '', $name);
        $name = str_replace('/', ' sur ', $name);
        // particular cases handed manually
        // => convert names as spelled in files E1 and E3 to the corresponding names in geonames
        if($name == 'Asnières'){
            $name = 'Asnières-sur-Seine';
            $dept = '92';
        }
        else if($name == 'Gaillac-sur-Tarn'){
            $name = 'Gaillac';
        }
        else if($name == 'Chalons sur Marne'){
            $name = 'Châlons-en-Champagne';
        }
        else if($name == 'Vielmur' && $dept == '81'){
            $name = 'Vielmur-sur-Agout';
        }
        else if($name == 'saint Maur' && $dept == '94'){
            $name = 'Saint-Maur-des-Fossés';
        }
        else if($name == 'Soisy sous Montmoren' && $dept == '78'){
            $name = 'Soisy-sous-Montmorency';
            $dept = '95';
        }
        else if($name == 'Romans' && $dept == '26'){
            $name = 'Romans-sur-Isère';
        }
/* 
        else if($name == '' && $dept == ''){
            $name = '';
        }
*/
        $slug = \lib::slugify($name);
        // HERE call to Geonames to match
        $geonames = \Geonames::match([
            'slug' => $slug,
            'countries' => ['FR'],
            'admin2-code' => $dept,
        ]);
        if($geonames){
            return [
                $geonames[0]['geoid'],
                $geonames[0]['name'],
                $geonames[0]['longitude'],
                $geonames[0]['latitude'],
            ];
        }
        // not matched
        self::$n_missing_places++;
        return ['', '', '', ''];
    }
    
    
}// end class    

