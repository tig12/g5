<?php
/********************************************************************************
    Parses file 1-raw/newalchemypress.com/3a_sports-utf8.csv
    and stores it to 5-tmp/newalch/4391SPO.csv
    
    3a_sports-utf8.csv is a modified version of file 3a_sports-utf8.txt
    This file was retrieved in april 2019 from
    https://newalchemypress.com/gauquelin/gauquelin_docs/3a_sports.txt
    Modifications done on the original file are detailed in
    1-raw/newalchemypress.com/3a_sports-utf8-README
    
    The file contains 4391 sportsmen used by Ertel
    
    @license    GPL
    @history    2019-05-10 12:19:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\init\Config;
use g5\patterns\Command;

class raw2csv implements Command{
    
    /**
        Mapping between country code used in the file (field NATION)       
        and ISO 3166 country code.
    **/
    const NATION_CY = [
        'USA' => 'US',
        'FRA' => 'FR',
        'ITA' => 'IT',
        'BEL' => 'BE',
        'GER' => 'DE',
        'SCO' => 'GB', // Scotland ; loss of information
        'NET' => 'NL',
        'LUX' => 'LU',
        'SPA' => 'ES',
    ];
    
        
    const OUTPUT_COLUMNS = [
        'QUEL',
        'NR',
        'FNAME',
        'GNAME',
        'DATE',
        'SPORT',
        'IG',
        'CY',
        'ZITRANG',
        'ZITSUM',
        'ZITATE',
        'ZITSUM_OD',
        'MARS',
        'MA_',
        'MA12',
        'G_NR',
        'PARA_NR',
        'CFEPNR',
        'CSINR',
        'G55',
        'SEX',
        'PUBL',
        'PHAS_',
        'AUFAB',
        'NIENCORR',
        'KURTZ',
        'GQBECORR',
        'CHRISNAME',
        'TAGMON',
        'ENG',
        'EXTEND',
        'NIENHUYS',
    ];
    
    // *****************************************
    /** 
        Parses file 1-raw/newalchemypress.com/3a_sports-utf8.csv
        and stores it to 5-tmp/newalch/4391SPO.csv
        @param $params empty array
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        $output = implode(Config::$data['CSV_SEP'], self::OUTPUT_COLUMNS) . "\n";
        
        $records = \lib::csvAssociative(Config::$data['dirs']['1-newalch-raw'] . DS . '3a_sports-utf8.csv');
        $N = count($records);
        for($i=1; $i < $N; $i++){
            if($i%2 == 0){
                continue; // every other line is empty
            }
            $record = $records[$i];
            $new = [];
            $new['QUEL'] = trim($record[' QUEL']);
            $new['NR'] = trim($record['  NR']);
            $new['FNAME'] = trim($record['NAME']);
            $new['GNAME'] = trim($record['VORNAME']);
            $new['DATE'] = self::compute_date($record);
            $new['SPORT'] = self::compute_profession(trim($record['SPORTART']));
            $new['IG'] = trim($record['INDGRUP']); // useless here, should be associated to profession
            $new['CY'] = self::NATION_CY[trim($record['NATION'])];
            $new['ZITRANG'] = trim($record['ZITRANG']);
            $new['ZITSUM'] = trim($record['ZITSUM']);
            $new['ZITATE'] = trim($record['ZITATE']);
            $new['ZITSUM_OD'] = trim($record['ZITSUM_OD']);
            $new['MARS'] = trim($record['MARS']);
            $new['MA_'] = trim($record['MA_']);
            $new['MA12'] = trim($record['MA12']);
            $new['G_NR'] = trim($record['G_NR']);
            $new['PARA_NR'] = trim($record['PARA_NR']);
            $new['CFEPNR'] = trim($record['CFEPNR']);
            $new['CSINR'] = trim($record['CSINR']);
            $new['G55'] = trim($record['GAUQ1955']);
            $new['SEX'] = trim($record['MF']);
            $new['PUBL'] = trim($record['PUBL']);
            $new['PHAS_'] = trim($record[' PHAS_']);
            $new['AUFAB'] = trim($record[' AUFAB']);
            $new['NIENCORR'] = trim($record['NIENCORR']);
            $new['KURTZ'] = trim($record['KURTZ']);
            $new['GQBECORR'] = trim($record['GQBECORR']);
            $new['CHRISNAME'] = trim($record['CHRISNAME']);
            $new['TAGMON'] = trim($record['TAGMON']);
            $new['ENG'] = trim($record['ENG']);
            $new['EXTEND'] = trim($record['EXTEND']);
            $new['NIENHUYS'] = trim($record['NIENHUYS']);
//echo "\n<pre>"; print_r($new); echo "</pre>\n"; exit;
//            echo $line . "\n";
            //echo 'NI = ' . trim($record['NIENHUYS'] . "\n";
            //echo "\n<pre>"; print_r(trim($record); echo "</pre>\n";
            $output .= implode(Config::$data['CSV_SEP'], $new) . "\n";
        }
        
        $outfile = Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::TMP_CSV_FILE;
        file_put_contents($outfile, $output);
        return "$outfile generated\n";
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    private static function compute_date(&$record){
    [$day, $hour] = [trim($record['GEBDATUM']), trim($record['STUND'])];
        $tmp = explode('.', $day);
        $date = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
        if($hour == ''){
            return $date;
        }
        $date .= ' ';
        $tmp = explode(',', $hour);
        if(count($tmp) == 1){
            $date .= str_pad ($hour , 2, '0', STR_PAD_LEFT) . ':00';
        }
        else{
            $date .= str_pad ($tmp[0] , 2, '0', STR_PAD_LEFT);
            $min = $tmp[1];
            if(strlen($min) == 1 && $min < 10){
                $min *= 10; // dirty patch because libre office truncated trailing zeroes
//echo $record[' QUEL'] . ' ' . $record[' NR'] . ' ' . $record['NAME'] . ' ' . $record['VORNAME'] . ' ' . "'$hour' '$day'\n";
            }
            $min = round($min * 0.6); // convert decimal part of hour to minutes
            $date .= ':' . str_pad ($min , 2, '0', STR_PAD_LEFT);
        }
        return $date;
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    public static function compute_profession($str){
        return $str; // todo
    }
    
}// end class    
