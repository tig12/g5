<?php
/********************************************************************************
    Generates a file in 9-g55-corrected/
    from corresponding files in 3-g55-edited/ and 5-cura-csv/
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-07-24 18:11:30+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\g55\all;

use g5\G5;
use g5\app\Config;
use g5\commands\g55\G55;
use g5\commands\gauq\LERRCP;
use tiglib\patterns\Command;
use g5\model\Libreoffice;

class genCorr implements Command {
    
    /** 
        Columns of generated files
        Same as Cura::TMP_CSV_COLUMNS
        except column NUM is replaced by ORIG
    **/
    const GEN_FIELDS = [
        'ORIG',
        'FNAME',
        'GNAME',
        'OCCU',
        'DATE',
        'DATE_C',
        'PLACE',
        'CY',
        'C2',
        'GEOID',
        'LG',
        'LAT',
        'NOTES',
    ];
    
    // *****************************************
    /** 
        Generates files in          9-g55-corrected/
        from csv files located in   3-g55-edited/
        and                         5-cura-csv/
        See 9-g55-corrected/README for a meaning of generated fields
        
        Called by : php run-g5.php g55 <filename> genCorr
        
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
                $new['DATE_C'] = '';
                $new['PLACE'] = $g55row['PLACE_55'];
                $new['CY'] = $g55row['CY_55'];
                $new['C2'] = $g55row['C2_55'];
                $new['GEOID'] = '';                                     
                $new['LG'] = '';
                $new['LAT'] = '';
                $new['NOTES'] = '';
                $res .= implode($new, G5::CSV_SEP) . "\n";
                continue;
            }
            $curarow =& $curarows[$NUM];
            $new['ORIG'] = LERRCP::gauquelinId($origin, $NUM);
            // copy cura row except for NUM field
            foreach($curarow as $k => $v){
                if($k == 'NUM'){
                    continue;
                }
                $new[$k] = $v;
            }
            $res .= implode($new, G5::CSV_SEP) . "\n";
        }
        $filename = Config::$data['dirs']['9-g55-corrected'] . DS . $g55group . '.csv';
        file_put_contents($filename, $res);
        $report .= "Wrote $filename\n";
        return $report;
    }
    
    
}// end class
