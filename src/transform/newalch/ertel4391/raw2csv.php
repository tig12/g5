<?php
/********************************************************************************
    Converts file 3a_sports-utf8.txt to a csv
    This file was retrieved in april 2019 from
    https://newalchemypress.com/gauquelin/gauquelin_docs/3a_sports.txt
    And then converted to utf-8.
    The file contains 4391 sportsmen used by Ertel
    
    @license    GPL
    @history    2019-05-10 12:19:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\init\Config;

class raw2csv{
    
    /**
        Mapping between country code used in the file (field NATION)
        and ISO 3166 country code.
    **/
    const NATION_COU = [
        'USA' => 'US',
        'FRA' => 'FR',
        'ITA' => 'IT',
        'BEL' => 'BE',
        'GER' => 'DE',
        'SCO' => 'GB', // Scotland ; loss of information
        'NET' => 'NL',
        'LUX' => 'LU',
    ];
    
    // *****************************************
    /** 
        Parses file 1-raw/newalchemypress.com/3a_sports-utf8.txt
        and stores it to 5-tmp/newalch/4391SPO.csv
        @return report
        @throws Exception if unable to parse
    **/
    public static function action(){
        $lines = file(Config::$data['dirs']['1-newalch-raw'] . DS . '3a_sports-utf8.txt');
echo trim($lines[4]) . "\n";
        $fieldnames = preg_split('/\s+/', trim($lines[4]));
        $N = count($lines);
        //for($i=6; $i < $N-3; $i++){
for($i=6; $i < 8; $i++){
            $line = ltrim($lines[$i]);
            if($line == ''){
                continue;
            }
            // split the line by fixed width
            $j = 0;
            $cur = [];
            $cur[$fieldnames[$j++]] = trim(substr($line, 0, 9));
            $cur[$fieldnames[$j++]] = trim(substr($line, 9, 3));
            $cur[$fieldnames[$j++]] = trim(substr($line, 12, 19));
            $cur[$fieldnames[$j++]] = trim(substr($line, 31, 21));
            $cur[$fieldnames[$j++]] = trim(substr($line, 52, 10));
            $cur[$fieldnames[$j++]] = trim(substr($line, 63, 5));
            $cur[$fieldnames[$j++]] = trim(substr($line, 69, 4));
            $cur[$fieldnames[$j++]] = trim(substr($line, 78, 8));
            $cur[$fieldnames[$j++]] = trim(substr($line, 86, 7));
            $cur[$fieldnames[$j++]] = trim(substr($line, 93, 7));
            $cur[$fieldnames[$j++]] = trim(substr($line, 101, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 108, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 115, 12));
            $cur[$fieldnames[$j++]] = trim(substr($line, 128, 4));
            $cur[$fieldnames[$j++]] = trim(substr($line, 133, 3));
            $cur[$fieldnames[$j++]] = trim(substr($line, 137, 4));
            $cur[$fieldnames[$j++]] = trim(substr($line, 142, 5));
            $cur[$fieldnames[$j++]] = trim(substr($line, 148, 7));
            $cur[$fieldnames[$j++]] = trim(substr($line, 156, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 163, 5));
            $cur[$fieldnames[$j++]] = trim(substr($line, 169, 8));
            $cur[$fieldnames[$j++]] = trim(substr($line, 178, 2));
            $cur[$fieldnames[$j++]] = trim(substr($line, 181, 4));
            $cur[$fieldnames[$j++]] = trim(substr($line, 186, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 193, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 200, 8));
            $cur[$fieldnames[$j++]] = trim(substr($line, 209, 5));
            $cur[$fieldnames[$j++]] = trim(substr($line, 215, 8));
            $cur[$fieldnames[$j++]] = trim(substr($line, 224, 9));
            $cur[$fieldnames[$j++]] = trim(substr($line, 234, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 241, 3));
            $cur[$fieldnames[$j++]] = trim(substr($line, 245, 6));
            $cur[$fieldnames[$j++]] = trim(substr($line, 252, 8));
            // Column 'L' dropped because contains nothing for all lines in newalch file
            // $cur[$fieldnames[$j++]] = trim(substr($line, 261, 1));
/*
QUEL     NR NAME               VORNAME              GEBDATUM   STUND SPORTART INDGRUP NATION ZITRANG ZITSUM ZITATE    ZITSUM_OD MARS MA_ MA12  G_NR PARA_NR CFEPNR CSINR GAUQ1955 MF PUBL  PHAS_  AUFAB NIENCORR KURTZ GQBECORR CHRISNAME TAGMON ENG EXTEND NIENHUYS L
G:A01    12 Acconcia           Italo                20.04.1925  2,50 FOOT     G       ITA          1      0                   0   26   1    9  1407                                  P    10,900 -7,700                                 0 20.04.
*/
            
            
            $new = [];
            $new['QUEL'] = $cur['QUEL'];
            $new['F_NAME'] = $cur['NAME'];
            $new['G_NAME'] = $cur['VORNAME'];
            $new['DATE'] = self::compute_date($cur['GEBDATUM'], $cur['STUND']);
            $new['PRO'] = self::compute_profession($cur['SPORTART']);
            $new['IG'] = $cur['INDGRUP'];
            $new['COU'] = self::NATION_COU[$cur['NATION']];
            $new['ZITRANG'] = $cur['ZITRANG'];
            $new['ZITSUM'] = $cur['ZITSUM'];
            $new['ZITATE'] = $cur['ZITATE'];
            $new['ZITSUM_OD'] = $cur['ZITSUM_OD'];
            $new['MARS'] = $cur['MARS'];
            $new['MA_'] = $cur['MA_'];
            $new['MA12'] = $cur['MA12'];
            $new['G_NR'] = $cur['G_NR'];
            $new['PARA_NR'] = $cur['PARA_NR'];
            $new['CFEPNR'] = $cur['CFEPNR'];
            $new['CSINR'] = $cur['CSINR'];
            $new['G55'] = $cur['GAUQ1955'];
            $new['SEX'] = $cur['MF'];
            $new['PUBL'] = $cur['PUBL'];
            $new['PHAS_'] = $cur['PHAS_'];
            $new['AUFAB'] = $cur['AUFAB'];
            $new['NIENCORR'] = $cur['NIENCORR'];
            $new['KURTZ'] = $cur['KURTZ'];
            $new['GQBECORR'] = $cur['GQBECORR'];
            $new['CHRISNAME'] = $cur['CHRISNAME'];
            $new['TAGMON'] = $cur['TAGMON'];
            $new['ENG'] = $cur['ENG'];
            $new['EXTEND'] = $cur['EXTEND'];
            $new['NIENHUYS'] = $cur['NIENHUYS'];
            
            echo $line . "\n";
            //echo 'NI = ' . $cur['NIENHUYS'] . "\n";
            echo "\n<pre>"; print_r($cur); echo "</pre>\n";
        }
        
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    private static function compute_date($day, $hour){
        $tmp = explode('.', $day);
        $date = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
        if($hour == ''){
            return $date;
        }
        $date .= ' ';
        $tmp = explode(',', $hour);
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    public static function compute_profession($str){
        return $str; // todo
    }
    
}// end class    
