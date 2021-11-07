<?php
/********************************************************************************
    Generates a file in 9-g55-original/
    from corresponding files in 3-g55-edited/ and 5-cura-csv/
    
    @license    GPL
    @history    2019-05-26 00:48:47+02:00, Thierry Graff : Creation
    @history    2019-07-01 23:39:53+02:00, Thierry Graff : New version
********************************************************************************/
namespace g5\commands\g55\all;

use g5\G5;
use g5\app\Config;
use g5\commands\g55\G55;
use g5\commands\gauq\LERRCP;
use tiglib\patterns\Command;
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
        'NOTES',
    ];
    
    // *****************************************
    /** 
        Generates files in 9-g55-original/ from csv files located in 3-g55-edited/
        See 9-g55-original/README for a meaning of generated fields
        
        Called by : php run-g5.php g55 <filename> genOrig
        
        @param $params Array containing 2 elements :
                       - the group to generate (like '570SPO')
                       - the name of this command (useless here)
        @return report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE - Useless parameter \"{$params[2]}\".\n";
        }
        
        $g55group = $params[0];
        
        $report = '';
        $report .= "\n=== php run-g5.php muller m5medics raw2csv ===\n";
        
        if(!G55::editedFileExists($g55group)){
            $report .= "Cannot compute $g55group because " . G55::editedFilename($g55group) . " does not exist\n";
            return $report;
        }
        
        [$origin, $g55rows, $curarows] = G55::prepareCuraMatch($g55group);
        
        $res = implode(self::GEN_FIELDS, G5::CSV_SEP) . "\n";
        
        // for restoration, don't loop on $g55rows because they don't contain ORIGIN=G55
        $g55full = G55::loadG55Edited($g55group);
        foreach($g55full as $g55row){                  
            $new = [];
            $NUM = $g55row['NUM'];
            if($g55row['ORIGIN'] == 'G55'){
                $new['ORIG'] = 'G55-' .  $NUM;
                $new['FNAME'] = $g55row['FAMILY_55'];
                $new['GNAME'] = $g55row['GIVEN_55'];
                $new['OCCU'] = $g55row['OCCU_55'];
                $new['DATE'] = $g55row['DAY_55'] . ' ' . Libreoffice::fix_hour($g55row['HOUR_55']);
                $new['PLACE'] = $g55row['PLACE_55'];
                $new['CY'] = $g55row['CY_55'];
                $new['C2'] = $g55row['C2_55'];
                //$new['GEOID'] = $curaRow[''];                                     
                $new['LG'] = ''; //$curaRow['LG_55'];
                $new['LAT'] = ''; //$curaRow['LAT_55'];
                $new['NOTES'] = '';
                $res .= implode($new, G5::CSV_SEP) . "\n";
                continue;
            }
            $curaRow =& $curarows[$NUM];                        
            $new['ORIG'] = LERRCP::gauquelinId($origin, $NUM);
            $new['FNAME'] = $g55row['FAMILY_55'] != '' ? $g55row['FAMILY_55'] : $curaRow['FNAME'];
            $new['GNAME'] = $g55row['GIVEN_55'] != '' ? $g55row['GIVEN_55'] : $curaRow['GNAME'];
            $new['OCCU'] = $g55row['OCCU_55'] != '' ? $g55row['OCCU_55'] : $curaRow['OCCU'];
            
            // date
            $d55 = $g55row['DAY_55'];
            $h55 = Libreoffice::fix_hour($g55row['HOUR_55']);
            $dcura = substr($curarows[$NUM]['DATE'], 0, 10);
            if($curarows[$NUM]['DATE_C'] != ''){ // 1937-09-17 18:00+01:00 or 1889-08-13 12:30+00:09:20
                $hcura = substr($curarows[$NUM]['DATE_C'], 11, 5);
                $tzcura = substr($curarows[$NUM]['DATE_C'], 16);
            }
            else{ // 1937-09-17 17:00:00+00:00
                $hcura = substr($curarows[$NUM]['DATE'], 11, 8);
                $tzcura = substr($curarows[$NUM]['DATE'], 19);
                if(substr($tzcura, -3) == ':00'){
                    $tzcura = substr($tzcura, 0, -3);
                }
            }                                                                                                                    
            $new['DATE'] = '';
            $new['DATE'] .= $d55 != '' ? $d55 : $dcura;
            $new['DATE'] .= ' ';
            $new['DATE'] .= $h55 != '' ? $h55 : $hcura;
            $new['DATE'] .= $tzcura;
            
            $new['PLACE'] = $g55row['PLACE_55'] != '' ? $g55row['PLACE_55'] : $curaRow['PLACE'];
            $new['CY'] = $g55row['CY_55'] != '' ? $g55row['CY_55'] : $curaRow['CY'];
            if($g55row['C2_55'] == 'NONE'){
                $new['C2'] = '';
            }
            else{
                $new['C2'] = $g55row['C2_55'] != '' ? $g55row['C2_55'] : $curaRow['C2'];
            }
            
            //$new['GEOID'] = $curaRow[''];
            $new['LG'] = $curaRow['LG'];
            $new['LAT'] = $curaRow['LAT'];
            $new['NOTES'] = $curaRow['NOTES'];
            
            $res .= implode($new, G5::CSV_SEP) . "\n";
        }
        $filename = Config::$data['dirs']['9-g55-original'] . DS . $g55group . '.csv';
        file_put_contents($filename, $res);
        $report .= "Wrote $filename\n";
        return $report;
    }
    
    
}// end class
