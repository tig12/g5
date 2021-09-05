<?php
/********************************************************************************
    Import data/raw/muller/afd2-men/muller-afd2-men.txt
    to data/tmp/muller/afd2-women/muller-afd2-men.csv
    
    @license    GPL
    @history    2021-09-05 05:09:35+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd2men;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
//use tiglib\arrays\sortByKey;

class raw2tmp implements Command {
    
    private static $cys = [
        'A'   => 'AT', // Austria
        'B'   => 'BE', // Belgium
        'CH'  => 'CH', // Switzerland
        'CS'  => 'CZ', // Czech Republic
        'D'   => 'DE', // Germany
        'DK'  => 'DK', // Denmark
        'DOP' => 'PL', // Former German regions, now Polish
        'DOS' => 'RU', // Former German regions, now Russia 
        'DZ'  => 'DZ', // Algeria
        'E'   => 'ES', // Spain
        'F'   => 'FR', // France
        'GB'  => 'GB', // Great Britain
        'I'   => 'IT', // Italy
        'L'   => 'LU', // Luxemburg
        'NL'  => 'NL', // Netherlands
        'S'   => 'SE', // Sweden
        'RCH' => 'CL', // Chile
        'USA' => 'US', // United States of America
    ];
    
    /**
        admin code level 1 of geonames.org
        Useful for CH
    **/
    private static $c1s = [
        'Baselland'         => 'BL',
        'Basel-Stadt'       => 'BS',
        'Bern'              => 'BE',
        'Ca.'               => 'CA',
        'Emmental, Bern'    => 'BE',
        'Graubünden'        => 'GR',
        'Ill.'              => 'IL',
        'Luzern'            => 'LU',
        'N.H.'              => 'NH',
        'N.J.'              => 'NJ',
        'Minn.'             => 'MN',
        'Nevenburg'         => 'NE',
        'Ohio'              => 'OH',
        'Pa.'               => 'PA',
        'St. Gallen'        => 'SG',
        'Waadt'             => 'VD',
        'Wash.'             => 'WA',
    ];
    
    /**
        admin code level 2 of geonames.org
        Match not done for AT, DE
    **/
    private static $c2s = [
        'Ancona'            => 'AN',
        'Ancona, Rom'       => 'AN',
        'Antwerpen'         => 'VAN',
        'Bologna'           => 'BO',
        'Briissel'          => 'BRU',
        'Calvados'          => '14',
        'Cher'              => '18',
        'Deux-Sévres'       => '79',
        //'Donan'             => '',
        'Dordogne'          => '19',
        'Dresden'           => '',
        //'Elster, Merseburg' => '',
        //'Erzgebirge'        => '',
        //'Fehrbellin, Brandenbg.' => '',
        //'Harz'              => '',
        //'Icking, Oberb.'    => '',
        //'Innsbruck, Tirol'  => '',
        //'Karnten'           => '',
        //'Lavanttal'         => '',
        //'Liitzen'           => '',
        'Lot'               => '46',
        //'Meifen, Sachsen'   => '',
        //'Oder'              => '',
        'Oise'              => '60',
        //'Ostpriegnitz'      => '',
        'Paris'             => '75',
        'Pavia, Lombardei'  => 'PV',
        //'Pegau, Sachsen'    => '',
        //'Rigen'             => '',
        //'Rochlitz, Sachsen' => '',
        'Rom'               => 'RM',
        //'Sachsen'           => '',
        'Sardinien'         => 'NU', // Nuoro
        'Seine, Paris'      => '75',
        //'Steiermark'        => '',
        //'Thüringen'         => '',
        //'Tirol'             => '',
        'Turin'             => 'TO',
        'Vendée'            => '85',
        'Yonne'             => '89',
    ];
    
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "USELESS PARAMETER : {$params[0]}\n";
        }
        
        $report =  "--- muller afd3 raw2tmp ---\n";
        
        $raw = AFD2::loadRawFile();
        $res = implode(G5::CSV_SEP, AFD2::TMP_FIELDS) . "\n";
        $res_raw = implode(G5::CSV_SEP, AFD2::RAW_FIELDS) . "\n";
        
        $nLimits = count(AFD2::RAW_LIMITS);
        $N = 0;
        $day = $hour = '';
        foreach($raw as $line){
            $N++;
            $new = array_fill_keys(AFD2::TMP_FIELDS, '');
            $new_raw = array_fill_keys(AFD2::RAW_FIELDS, '');
            for($i=0; $i < $nLimits-1; $i++){
                $rawFieldname = AFD2::RAW_FIELDS[$i];
                $offset = AFD2::RAW_LIMITS[$i];
                $length   = AFD2::RAW_LIMITS[$i+1] - AFD2::RAW_LIMITS[$i];
                $field = trim(mb_substr($line, $offset, $length));
                $new_raw[$rawFieldname] = $field;
                switch($rawFieldname){
                case 'NAME':
                    [$new['FNAME'], $new['GNAME'], $new['NOBL']] = self::computeName($field);
                break;
                case 'DATE':
                    $day = self::computeDay($field);
                break;
                case 'TIME':
                    $hour = self::computeHour($field);
                break;
                case 'TZO':
                    $new['TZO'] = self::computeTimezoneOffset($field);
                break;
                case 'PLACE':
                    // by chance, CY appears before place in raw file => can be passed here
                    [$new['C1'], $new['C2'], $new['PLACE']] = self::computePlace($field, $new['CY']);
                break;
                case 'LAT':
                    $new['LAT'] = self::computeLat($field);
                break;
                case 'CY':
                    $new['CY'] = self::$cys[$field];
                break;
                case 'LG':
                    $new['LG'] = self::computeLg($field);
                break;
                // other fields are simply copied
                default:
                    $new[$rawFieldname] = $field;
                break;
                }
            }
//echo "\n"; print_r($new_raw); echo "\n";
//echo "\n"; print_r($new); echo "\n";
//if($N >2)
//exit;
            $new['DATE'] = "$day $hour";
            $res .= implode(G5::CSV_SEP, $new) . "\n";
            $res_raw .= implode(G5::CSV_SEP, $new_raw) . "\n";
        }
        
        $outfile = AFD2::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            $report .= "Created directory $dir\n";
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outfile, $res);
        $report .= "Stored $N records in $outfile\n";
        
        $outfile = AFD2::tmpRawFilename();
        file_put_contents($outfile, $res_raw);
        $report .= "Stored $N records in $outfile\n";
        
        return $report;
    }
    
    
    private static function computeLat($str) {
        $tmp = explode(' N ', $str);
        $multiply = 1;
        if(count($tmp) != 2){
            $tmp = explode(' S ', $str);
            $multiply = -1;
        }
        return $multiply * round($tmp[0] + $tmp[1] / 60, 2);
    }
    
    private static function computeLg($str) {
        // use preg_split instead of explode(' ', $str) because of strings like
        // '005 E  2' (instead of '005 E 02')
        $tmp = preg_split('/\s+/', $str);
// echo "lg = $str\n";
// echo "\n"; print_r($tmp); echo "\n";
// exit;
        $res = $tmp[0] + $tmp[2] / 60;
        $res = $tmp[1] == 'W' ? -$res : $res;
        return round($res, 2);
    }
    
    private static function computeHour($hour) {
        return str_replace('.', ':', $hour);
    }
    
    private static function computeDay($str) {
        $tmp = explode('.', $str);
        if(count($tmp) != 3){
            echo "ERROR DAY $str\n";
            return $str;
        }
        return implode('-', [$tmp[2], $tmp[1], $tmp[0]]);
    }
    
    /**
        @return [$new['FNAME'], $new['GNAME'], $new['NOBL']]
    **/
    private static function computeName($str): array {
        $fname = $gname = $nobl = '';
        // delete content between parentheses
        // concern 2 records, managed by tweak files
        $str1 = preg_replace('/.*?\((.*?)\).*?/', '', $str);
        $str1 = $str;
//echo "\nstr ='$str'\nstr1='$str1'\n";
echo "$str\n";
return ['', '', ''];
        if(count($m) == 2){
            $oname1 = $m[1];
            $str1 = trim(str_replace("($oname1)" , '', $str));
        }
        $tmp = explode(',', $str1);
        if(count($tmp) == 3){
            $oname2 = trim($tmp[2]);
        }
        else if(count($tmp) != 2){
            echo "================ ERROR NAME ================ $str\n";
            return [$str, '', '', ''];
        }
        $fname = trim($tmp[0]);
        $gname = trim($tmp[1]);
        // handle * in gname
        $tmp = explode('*', $gname);
        if(count($tmp) == 2){
            $gname = trim($tmp[0]);
            $oname3 = trim($tmp[1]);
        }
        $fname = ucwords(strtolower($fname), '- ');
        return [$fname, $gname, $oname1, $oname2, $oname3];
    }
    
    private static function computePlace($str, $cy): array {
        // content between parentheses => existence of C1 or C2
        $c1 = $c2 = '';
        $place = $str;
        preg_match('/.*?\((.*?)\)/', $str, $m);
        if(count($m) == 0){
            return [$c1, $c2, $place];
        }
        if(count($m) != 2){
            echo "================ ERROR PLACE ================ $str\n";
            return [$c1, $c2, $place];
        }
        $test = $m[1];
        if($test == 'Dresden'){
            // particular case, indicates a nearby city, not admin division
            return ['13', $c2, $place];
        }
        if(in_array($cy, ['DE', 'AT'])){
            // indications in parentheses are confused, sometimes refer to c1, sometimes to c2
            // sometimes to city => didn't try exhaustive match.
            return [$c1, $c2, $place];
        }
        $place = trim(str_replace("($test)" , '', $str));
        $c1 = self::$c1s[$test] ?? '';
        $c2 = self::$c2s[$test] ?? '';
        return [$c1, $c2, $place];
    }
    
    private static function computeTimezoneOffset($str): string {
        if($str == ''){
            return '';
        }
        preg_match('/(-?)(\d+)\.(\d+)/', $str, $m);
        array_shift($m);
        [$sign1, $hour1, $min1] = $m;
        // Müller's sign is inverse of ISO 8601
        $sign = $sign1 == '' ? '-' : '+';
        if((int)$hour1 == 0 && (int)$min1 == 0){
            $sign = '+';
        }
        $hour = str_pad($hour1, 2, '0', STR_PAD_LEFT);
        // $min1 is a decimal fraction of hour
        $min = round($min1 * 0.6); // *60 / 100
        $min = str_pad($min, 2, '0', STR_PAD_LEFT);
        $res = "$sign$hour:$min";
        return $res;
    }
    
}// end class    

