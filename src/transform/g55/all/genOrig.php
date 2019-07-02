<?php
/********************************************************************************
    Generates a file in 9-g55-original/
    from corresponding files in 3-g55-edited/ and 5-cura-csv/
    
    @license    GPL
    @history    2019-05-26 00:48:47+02:00, Thierry Graff : Creation
    @history    2019-07-01 23:39:53+02:00, Thierry Graff : New version
********************************************************************************/
namespace g5\transform\g55\all;

use g5\G5;
use g5\Config;
use g5\transform\g55\G55;
use g5\transform\cura\Cura;
use g5\patterns\Command;
use g5\model\Libreoffice;

class genOrig implements Command {
    
    const GEN_FIELDS = [
        'ORIG',
        'FNAME',
        'GNAME',
        'OCCU',
        'DATE',
        'PLACE',
        'CY',
        'C2',
        //'GEOID',
        'LG',
        'LAT',
        // 'COMMENT',
    ];
    
    // *****************************************
    /** 
        Generates files in 9-g55-original/ from csv files located in 3-g55-edited/
        See 9-g55-original/README for a meaning of generated fields
        
        Called by : php run-g5.php g55 <filename> edited2original
        
        @param $params Array containing 2 elements :
                       - the group to generate (like '570SPO')
                       - the name of this command (useless here)
        @return report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE - Useless parameter \"{$params[2]}\".\n";
        }
        
        $g55Group = $params[0];
        [$origin, $g55Rows, $curaRows] = G55::prepare($g55Group);
        
        $res = implode(self::GEN_FIELDS, G5::CSV_SEP) . "\n";
        $report = '';
        
        foreach($g55Rows as $NUM => $g55Row){
            $curaRow =& $curaRows[$NUM];
// echo "\n"; print_r($curaRow); echo "\n";
// echo "\n"; print_r($g55Row); echo "\n";
            $new = [];
            $new['ORIG'] = Cura::orig($origin, $NUM);
            $new['FNAME'] = $g55Row['FAMILY_55'] != '' ? $g55Row['FAMILY_55'] : $curaRow['FNAME'];
            $new['GNAME'] = $g55Row['GIVEN_55'] != '' ? $g55Row['GIVEN_55'] : $curaRow['GNAME'];
            $new['OCCU'] = $g55Row['OCCU_55'] != '' ? $g55Row['OCCU_55'] : $curaRow['OCCU'];
            
            // date
            $d55 = $g55Row['DAY_55'];
            $h55 = Libreoffice::fix_hour($g55Row['HOUR_55']);
            
//$curaRows[$NUM]['DATE_C'] = '1917-04-11 14:20+01:00';
//echo "curaRows[$NUM]['DATE_C'] = " . $curaRows[$NUM]['DATE_C'] . "\n";
            $dcura = substr($curaRows[$NUM]['DATE'], 0, 10);
            if($curaRows[$NUM]['DATE_C'] != ''){ // 1937-09-17 18:00+01:00 or 1889-08-13 12:30+00:09:20
                $hcura = substr($curaRows[$NUM]['DATE_C'], 11, 5);
                $tzcura = substr($curaRows[$NUM]['DATE_C'], 16);
// echo "hcura = $hcura\n";
// echo "dcura = $dcura\n";
// echo "tzcura = $tzcura\n";
            }
            else{ // 1937-09-17 17:00:00+00:00
                $hcura = substr($curaRows[$NUM]['DATE'], 11, 8);
                $tzcura = substr($curaRows[$NUM]['DATE'], 19);
                if(substr($tzcura, -3) == ':00'){
                    $tzcura = substr($tzcura, 0, -3);
                }
            }                                                                                                                    
            $new['DATE'] = '';
            $new['DATE'] .= $d55 != '' ? $d55 : $dcura;
            $new['DATE'] .= ' ';
            $new['DATE'] .= $h55 != '' ? $h55 : $hcura;
            $new['DATE'] .= $tzcura;
            
            $new['PLACE'] = $g55Row['PLACE_55'] != '' ? $g55Row['PLACE_55'] : $curaRow['PLACE'];
            $new['CY'] = $g55Row['CY_55'] != '' ? $g55Row['CY_55'] : $curaRow['CY'];
            if($g55Row['C2_55'] == 'NONE'){
                $new['C2'] = '';
            }
            else{
                $new['C2'] = $g55Row['C2_55'] != '' ? $g55Row['C2_55'] : $curaRow['C2'];
            }
            
            //$new['GEOID'] = $curaRow[''];
            $new['LG'] = $curaRow['LG'];
            $new['LAT'] = $curaRow['LAT'];
            
            $res .= implode($new, G5::CSV_SEP) . "\n";
//echo "$res\n"; exit;
        }
        $filename = Config::$data['dirs']['9-g55-original'] . DS . $g55Group . '.csv';
        file_put_contents($filename, $res);
        $report .= "Wrote $filename\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Auxiliary of self::generate()
        @param $date_cura   content of column DATE
        @param $day_c       content of column DAY_C
        @param $hour_c      content of column HOUR_C
        @return Array containing day and hour in format [YYYY-MM-DD, HH:MM:SS]
        @throws Exception
                    - in case of incoherence between cura and corrected data
                    - in case of incomplete data
    **/
    private static function computeBirthDate($date_cura, $day_c, $hour_c){
        if($day_c == '' && $hour_c == ''){
            // no checks on $date_cura, supposed correct
            $day = substr($date_cura, 0, 10);
            $hour = substr($date_cura, 11, 8);
            return [$day, $hour];
        }
        if($date_cura == ''){
            // happens for some rows, present in Gauquelin book, and not in cura data (ex Jacques Lunnis)
            if($day_c == ''){
                throw new \Exception("Missing column DAY_C");
            }                                                                                                         
            if($hour_c == ''){
                throw new \Exception("Missing column HOUR_C");
            }
        }
        if(is_numeric($hour_c)){
            // ex : convert '14' to '14:00:00'
            // obliged to do that because of libre office automatic conversion
            $hour_c = str_pad($hour_c, 2, '0', STR_PAD_LEFT) . ':00:00';
        }
        //
        $day = substr($date_cura, 0, 10);
        $hour = substr($date_cura, 11, 8);
        // override with corrected values
        if($day_c != ''){
            $day = $day_c;
        }
        if($hour_c != ''){
            $hour = $hour_c;
        }
        return [$day, $hour];
    }
    
    
}// end class
