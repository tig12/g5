<?php
/********************************************************************************
    Add fields TZO, DATE-UT an NOTES-DATE to data/tmp/gauq/lerrcp/D6.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2025-08-16 20:21:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\D6;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use tiglib\arrays\csvAssociative;
use tiglib\timezone\offset;
use tiglib\time\sub;

class addTzo implements Command {
    
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - this command doesn't need this parameter\n";
        }
        
        $datafile = 'D6';
        $report =  "--- gauq $datafile addTzo ---\n";
        
        $rows = LERRCP::loadTmpFile_num($datafile);
        $rowsGeo = csvAssociative::compute(D6::GEONAMES_FILE);
        $res = implode(G5::CSV_SEP, D6::TMP_FIELDS) . "\n";
        foreach($rowsGeo as $rowGeo){
            $NUM = $rowGeo['NUM'];
            $new = $rows[$NUM];
            if(offset::isCountryImplemented($new['CY'])){
                [$offset, $code, $err] = offset::computeTiglib(
                        $new['CY'],
                        $new['DATE'],
                        $new['LG'],
                        $new['C2']
                );
                if($err == ''){
                    $new['TZO'] = $offset;
                    $new['DATE-UT'] = sub::execute($new['DATE'], $offset);
                }
                else{
                    $new['NOTES-DATE'] = $code;
                }
            }
            $res .= implode(G5::CSV_SEP, $new) . "\n";
        }
        $outfile = LERRCP::tmpFilename($datafile);
        file_put_contents($outfile, $res);
        $report .= "Added TZO, DATE-UT and NOTES-DATE to $outfile\n";
        return $report;
    }
    
}// end class    
