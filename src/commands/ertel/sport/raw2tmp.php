<?php
/********************************************************************************
    Converts file 3a_sports-utf8.txt to a csv
    This file was retrieved in april 2019 from
    https://newalchemypress.com/gauquelin/gauquelin_docs/3a_sports.txt
    The file contains 4384 sportsmen used by Ertel.
    
    Generates 2 files :
        - data/tmp/ertel/ertel-4384-athletes.csv, to work on the file.
        - data/tmp/ertel/ertel-4384-athletes-raw.csv, to keep an exact copy of of the original fields.
    The unique utility of ertel-4384-athletes-raw.csv is to fill the field "raw" of persons in db.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-10 12:19:50+02:00, Thierry Graff : creation
    @history    2020-08-12 19:20:17+02:00, Thierry Graff : add generation of 4391SPO-raw.csv
********************************************************************************/
namespace g5\commands\ertel\sport;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\Newalch;

class raw2tmp implements Command {
    
    /** 
        Imports file data/raw/ertel/3a_sports-utf8.txt to data/tmp/ertel.
        @param $params  Empty array
        @return report
    **/
    public static function execute($params=[]): string {
        
        $filename = ErtelSport::rawFilename();
        if(!is_file($filename)){
            return "ERROR : Missing file $filename\n";
        }
        
        $report = "--- ertel sport raw2tmp ---\n";
        
        $lines = file($filename);
        $output = $output_raw = '';
        
        $N = count($lines);
        $nRes = 0;
        for($i=6; $i < $N-3; $i++){
            $line = $lines[$i];
            if(trim($line) == ''){
                continue;                                                                                                   
            }
            $new = [];
            $new['GQID']        = '';
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
            $new['CY'] = ErtelSport::RAW_NATION_CY[$country];
            $new['C1'] = ($country == 'SCO' ? 'SCT' : ''); // SCT = geonames code for Scotland
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
            $new['SEX'] = ($gender == 'F' ? 'F' : 'M');
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
            //
            $new['GQID'] = ErtelSport::GQIDfrom3a_sports($new);
            //
            // one particular case: Beltoise Jean Pierre 1937-04-26
            if($new['NR'] == 332){
                $new['GQID'] = 'E3-95';
            }
            
            // build a raw record, exact copy of the original file
            $raw = [
                'QUEL'      => $new['QUEL'],
                'NR'        => $new['NR'],
                'NAME'      => $new['FNAME'],
                'VORNAME'   => $new['GNAME'],
                'GEBDATUM'  => $date,
                'STUND'     => $hour,
                'SPORTART'  => $new['SPORT'],
                'INDGRUP'   => $new['IG'],
                'NATION'    => $country,
                'ZITRANG'   => $new['ZITRANG'],
                'ZITSUM'    => $new['ZITSUM'],
                'ZITATE'    => $new['ZITATE'],
                'ZITSUM_OD' => $new['ZITSUM_OD'],
                'MARS'      => $new['MARS'],
                'MA_'       => $new['MA_'],
                'MA12'      => $new['MA12'],
                'G_NR'      => $new['G_NR'],
                'PARA_NR'   => $new['PARA_NR'],
                'CFEPNR'    => $new['CFEPNR'],
                'CSINR'     => $new['CSINR'],
                'GAUQ1955'  => $new['G55'],
                'MF'        => $gender,
                'PUBL'      => $new['PUBL'],
                'PHAS_'     => $new['PHAS_'],
                'AUFAB'     => $new['AUFAB'],
                'NIENCORR'  => $new['NIENCORR'],
                'KURTZ'     => $new['KURTZ'],
                'GQBECORR'  => $new['GQBECORR'],
                'CHRISNAME' => $new['CHRISNAME'],
                'TAGMON'    => $new['TAGMON'],
                'ENG'       => $new['ENG'],
                'EXTEND'    => $new['EXTEND'],
                'NIENHUYS'  => $new['NIENHUYS'],
                'L'         => '',
            ];
            $nRes++;
            $output .= implode(G5::CSV_SEP, $new) . "\n";          
            $output_raw .= implode(G5::CSV_SEP, $raw) . "\n";
        }
        $output = implode(G5::CSV_SEP, array_keys($new)) . "\n" . $output;       
        $output_raw = implode(G5::CSV_SEP, array_keys($raw)) . "\n" . $output_raw;

        // store tmp file 
        $outfile = ErtelSport::tmpFilename();
        $dir = dirname($outfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($outfile, $output);
        $report .=  "Generated $nRes records in $outfile\n";
        
        // store tmp raw file 
        $outfile = ErtelSport::tmpRawFilename();
        file_put_contents($outfile, $output_raw);
        $report .=  "Generated $nRes records in $outfile\n";
        
        return $report;
    }
    
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
