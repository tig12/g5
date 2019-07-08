<?php
/********************************************************************************
    Imports 1-newalch-raw/05-muller-medics/5a_muller-medics-utf8.txt to 5-newalch-csv/1083MED.csv
        
    @license    GPL
    @history    2019-07-06 12:21:23+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\muller1083;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;

class raw2csv implements Command{
    
    // *****************************************
    /** 
        Parses file 1-raw/newalchemypress.com/3a_sports-utf8.csv
        and stores it to 5-tmp/newalch/4391SPO.csv
        @param $params empty array
        @return report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "INVALID PARAMETER : " . $params[0] . " - raw2csv doesn't need this parameter\n";
        }
        $pName = '/(.*?)\((.*?)\)/';
        $pPlace = '/(.*?)\((.*?)\)/';
        $lines = file(Config::$data['dirs']['1-newalch-raw'] . DS . '05-muller-medics' . DS . '5a_muller-medics-utf8.txt');
        
        $N = count($lines);
        
        $res = implode(G5::CSV_SEP, Muller1083::TMP_CSV_COLUMNS) . "\n";
        $nRecords = 0;
        
//        for($i=5; $i < 12; $i++){
        for($i=5; $i < $N-3; $i++){
            if($i%2 == 1){
                continue;
            }
            $nRecords++;
            
            $line  = trim($lines[$i]);
            $len = strlen($line);
            // Because of accentuated characters in name and place, strpos needs to be adjusted
            // $delta = nb of accentuated characters found between begin of line an current position
            $delta = 0;
            
            $new = [];
            $new['NR'] = trim(substr($line, 0, 5));
echo $new['NR'] . " ";
            $new['SAMPLE'] = trim(substr($line, 5, 11));
            $new['GNR'] = trim(substr($line, 16, 6));
            $new['CODE'] = trim(substr($line, 32, 1));
            // name
            $tmp = trim(substr($line, 34, 51));
            $delta += ( strlen(utf8_encode($tmp)) - strlen($tmp) ) / 2;
            preg_match($pName, $tmp, $m);
            $new['FNAME'] = utf8_encode(trim(ucwords(strtolower($m[1]))));
            $new['GNAME'] = utf8_encode(trim(ucwords(strtolower($m[2]))));
            // date
            $tmp = explode('.', trim(substr($line, 85 + $delta, 10)));
            $new['DATE'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
            $new['DATE'] .= ' ' . str_replace('.', ':', substr($line, 101 + $delta, 5));
            // place
            $tmp = trim(substr($line, 110 + $delta, 38));
            preg_match($pPlace, $tmp, $m);
            $new['PLACE'] = utf8_encode(trim($m[1]));
            $delta += ( strlen($new['PLACE']) - strlen(trim($m[1])) ) / 2;
            $delta += ( strlen(utf8_encode(trim($m[2]))) - strlen(trim($m[2])) ) / 2;
            $new['C2'] = ''; // @todo
echo "$delta\n";
            $new['LG'] = self::compute_lgLat(trim(substr($line, 146 + $delta, 8)));
            $new['LAT'] = self::compute_lgLat(trim(substr($line, 156 + $delta, 7)));
            $new['MODE'] = trim(substr($line, 168 + $delta, 3));
            $new['KORR'] = trim(substr($line, 173 + $delta, 5));
            $new['ELECTDAT'] = trim(substr($line, 184 + $delta, 10));
            $new['STBDATUM'] = trim(substr($line, 204 + +$delta, 10));
            // ELECTAGE not done (duplicate information, can be recomputed)
            // here are 14 fields, present in all lines and not containing spaces, so shorthcut.
            [
                $new['SONNE'],
                $new['MOND'],
                $new['VENUS'],
                $new['MARS'],
                $new['JUPITER'],
                $new['SATURN'],
                $new['SO_'],
                $new['MO_'],
                $new['VE_'],
                $new['MA_'],
                $new['JU_'],
                $new['SA_'],
                $new['PHAS_'],
                $new['AUFAB']
            ] =  preg_split('/\s+/', trim(substr($line, 218 + $delta, 71)));
            $new['PHAS_'] = str_replace(',', '.', $new['PHAS_']);
            $new['AUFAB'] = str_replace(',', '.', $new['AUFAB']);
            $new['NIENMO'] = trim(substr($line, 295 + $delta, 1));
            $new['NIENVE'] = trim(substr($line, 302 + $delta, 1));
            $new['NIENMA'] = trim(substr($line, 309 + $delta, 1));
            $new['NIENJU'] = trim(substr($line, 316 + $delta, 1));
            $new['NIENSA'] = trim(substr($line, 323 + $delta, 1));
            // GEBJAHR not done (duplicate information)
            // GEBMONAT not done (duplicate information)
            // GEBTAG not done (duplicate information)
            $res .= implode(G5::CSV_SEP, $new) . "\n";
//echo "\n"; print_r($new); echo "\n";
        }
/*                                                         
1    MUER_NUR                   1 ABADIE (BERNARD).                                  23.02.1817 1817 02.00    Mazerolles (Hautes-Pyrénées)        000 E 05   43 N 14    LMT             31.01.1888     71,0 18.10.1888    30   22    26   33       1     30   1   0   1   0   2   1 37,200   8,80                    0      2      2    1817        2     23

2    MUERGAUQ   SA22            3 ABADIE (JEAN BAPTISTE MARIE JULES).                12.08.1876 1876 03.00    Blaye (Gironde)                     000 W 34   44 N 50    LMT             26.05.1936     59,8 10.08.1953    26    2    30   25      19      6   1   2   1   0   1   1 60,200 -10,20      2      2      0      2      0    1876        8     12
478  MUERGAUQ   SA21049         3 HERMANN (HENRI XAVIER).                            19.12.1892 1892 20.00    Lunéville (Meurthe-et-Moselle)      006 E 12   48 N 42         -0.16      16.11.1943     50,9   .  .        23   23    26   13      11     30   0   0   1   1   2   1  0,200  -1,50                    0      2      2    1892       12     19
14   MUERGAUQ   ND129           3 ARDOIN (FRANÇOIS GUSTAVE EDMOND).                  03.11.1897 1897 10.00    Bourges (Cher)                      002 E 23   47 N 05         -0.16      28.05.1957     59,6   .  .         6   31     9    6      11      3   1   1   2   1   2   2 64,700  10,40             2      0      2      2    1897       11      3

*/
            
//echo "$res\n";
//exit;
        
        $outfile = Config::$data['dirs']['5-newalch-csv'] . DS . Muller1083::TMP_CSV_FILE;
        file_put_contents($outfile, $res);
        return "Importing Müller 183 - $nRecords records\n$outfile generated\n";
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2csv()
    **/
    private static function compute_lgLat($str){
        $tmp = explode(' ', $str);
if(count($tmp) != 3){
echo "$str\n"; exit;
}
        $res = $tmp[0] + $tmp[2] / 60;
        if($tmp[2] == 'S' || $tmp[2] == 'W'){
            $res = -$res;
        }
        return round($res, 5);
    }
    
}// end class    
