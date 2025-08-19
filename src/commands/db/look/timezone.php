<?php
/******************************************************************************
    Observe timezone computation in the database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2025-08-14 12:49:14+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\commands\db\look;

use tiglib\patterns\Command;
use g5\model\DB5;

class timezone implements Command {
    
    /** 
        @param  $params empty array
        @return Report
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        $res = [];
        
        $g5link = DB5::getDblink();
        $stmt = $g5link->query("select birth from person");
        
        $res = []; // assoc array. keys = country code, values = nb of null / not null tzo for this country
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $birth = json_decode($row['birth'], true);
            $tzo = $birth['tzo'];
            $cy = $birth['place']['cy'];
            if(!isset($res[$cy])){
                $res[$cy] = [
                    'not-null' => 0,
                    'null' => 0,
                ];
            }
            if(is_null($tzo)){
                $res[$cy]['null'] ++;
            }
            else{
                $res[$cy]['not-null'] ++;
            }
        }
        
        $return = '';
        $return .= "------------------------------------\n";
        $return .= "Country  Null     Not null    % null\n";
        $return .= "------------------------------------\n";
        $total_null = 0;
        $total_not_null = 0;
        foreach($res as $cy => $data){
            $total_null += $data['null'];
            $total_not_null += $data['not-null'];
            $percent = 100 * $data['null'] / ($data['null'] + $data['not-null']);
            $return .=
                  str_pad($cy, 9)
                . str_pad($data['null'], 9)
                . str_pad($data['not-null'], 12)
                . round($percent, 2) . ' %'
                . "\n";
        }
        $percent = 100 * $total_null / ($total_null + $total_not_null);
        $return .= "------------------------------------\n";
        $return .= "Total null     = $total_null\n";
        $return .= "Total not null = $total_not_null\n";
        $return .= "% null         = $percent %\n";
        
        return $return;
    }
    
} // end class
