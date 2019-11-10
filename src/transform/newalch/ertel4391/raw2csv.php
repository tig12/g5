<?php
/********************************************************************************
    Converts file 3a_sports-utf8.txt to a csv
    This file was retrieved in april 2019 from
    https://newalchemypress.com/gauquelin/gauquelin_docs/3a_sports.txt
    The file contains 4387 sportsmen used by Ertel
    
    @license    GPL
    @history    2019-05-10 12:19:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\G5;
use g5\Config;
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
    
        
    // *****************************************
    /** 
        Parses file 1-raw/newalchemypress.com/3a_sports-utf8.txt
        and stores it to 5-tmp/newalch/4391SPO.csv
        @return report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        $filename = Config::$data['dirs']['1-newalch-raw'] . DS . '03-ertel' . DS . '3a_sports-utf8.txt';
        if(!is_file($filename)){
            return "Missing file $filename\n";
        }
        
        $lines = file($filename);
        $output = '';
        
        $N = count($lines);
        for($i=6; $i < $N-3; $i++){
            $line = $lines[$i];
            if(trim($line) == ''){
                continue;                                                                                                   
            }
            $new = [];
            $new['GNUM']        = '';
            $new['QUEL']        = trim(mb_substr($line, 0, 6));
            if($new['QUEL'] == '*G:D10'){
                $new['QUEL'] = 'G:D10';
            }
            $new['NR']          = trim(mb_substr($line, 7, 6));
            $new['FNAME']       = trim(mb_substr($line, 13, 19));
            $new['GNAME']       = trim(mb_substr($line, 32, 21));
            $date               = trim(mb_substr($line, 53, 11));
            $hour               = trim(mb_substr($line, 64, 6));
            $new['DATE'] = self::compute_date($date, $hour);
            $new['SPORT']       = trim(mb_substr($line, 70, 6));
            $new['IG']          = trim(mb_substr($line, 79, 1));
            $country            = trim(mb_substr($line, 87, 3));
            $new['CY'] = self::NATION_CY[$country];
            $new['ZITRANG']     = trim(mb_substr($line, 100, 1));
            $new['ZITSUM']      = trim(mb_substr($line, 107, 1));
            $new['ZITATE']      = trim(mb_substr($line, 109, 16));
            $new['ZITSUM_OD']   = trim(mb_substr($line, 127, 1));
            $new['MARS']        = trim(mb_substr($line, 131, 2));
            $new['MA_']         = trim(mb_substr($line, 136, 1));
            $new['MA12']        = trim(mb_substr($line, 140, 2));
            $new['G_NR']        = trim(mb_substr($line, 144, 4));
            $new['PARA_NR']     = trim(mb_substr($line, 149, 5));
            $new['CFEPNR']      = trim(mb_substr($line, 157, 6));
            $new['CSINR']       = trim(mb_substr($line, 164, 5));
            $new['G55']         = trim(mb_substr($line, 170, 1));
            $gender             = trim(mb_substr($line, 179, 1));
            $new['G'] = ($gender == 'F' ? 'F' : 'M');
            $new['PUBL']        = trim(mb_substr($line, 182, 1));
            $new['PHAS_']       = trim(mb_substr($line, 187, 6));
            $new['AUFAB']       = trim(mb_substr($line, 194, 6));
            $new['NIENCORR']    = trim(mb_substr($line, 201, 8));
            $new['KURTZ']       = trim(mb_substr($line, 210, 5));
            $new['GQBECORR']    = trim(mb_substr($line, 216, 8));
            $new['CHRISNAME']   = trim(mb_substr($line, 233, 1));
            $new['TAGMON']      = trim(mb_substr($line, 235, 6));
            $new['ENG']         = trim(mb_substr($line, 244, 1));
            $new['EXTEND']      = trim(mb_substr($line, 251, 3));
            $new['NIENHUYS']    = trim(mb_substr($line, 260, 6));
            // Column 'L' dropped because contains nothing for all lines in newalch file
            $new['GNUM'] = self::compute_GNUM($new);
            
            $output .= implode(G5::CSV_SEP, $new) . "\n";
        }
        $output = implode(G5::CSV_SEP, array_keys($new)) . "\n" . $output;

        $outfile = Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::TMP_CSV_FILE;
        file_put_contents($outfile, $output);
        return "$outfile generated\n";
        
    }
    
    
    // ******************************************************
    /**
        Auxiliary of execute()
        Computes field GNUM, string like "A1-123"
    **/
    private static function compute_GNUM(&$new){
        if(substr($new['QUEL'], 0, 2) != 'G:'){
            return '';
        }
        $GNUM = '';
        $rest = substr($new['QUEL'], 2);
        switch($rest){
        	case 'A01': $GNUM = 'A1'; break;
        	case 'D06': $GNUM = 'D6'; break;
        	case 'D10': $GNUM = 'D10'; break;
        }
        $GNUM .= '-' . $new['G_NR'];
        return $GNUM;
    }
    
    
    // ******************************************************
    /**
        Auxiliary of execute()
    **/
    private static function compute_date($day, $hour){
        $tmp = explode('.', $day);
        $date = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
        if($hour == ''){
            return $date;
        }
        $date .= ' ';
        $tmp = explode(',', $hour);
        if(count($tmp) == 1){
            $date .= str_pad($hour , 2, '0', STR_PAD_LEFT) . ':00';
        }
        else{
            $date .= str_pad($tmp[0] , 2, '0', STR_PAD_LEFT);
            $min = round($tmp[1] * 0.6); // convert decimal part of hour to minutes
            $date .= ':' . str_pad($min , 2, '0', STR_PAD_LEFT);
        }
        return $date;
    }
    
    
}// end class    
