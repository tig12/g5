<?php
/********************************************************************************
    Extracts informations from 5-tmp/newalch-csv/4391SPO.csv
    @license    GPL
    @history    2019-05-11 18:58:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\ertel4391;

use g5\init\Config;
use g5\patterns\Command;

class extract implements Command {
    
    // *****************************************
    /** 
        Routes to the different actions, based on $param
        @param $param Array containing one element (a string)
                Can be : 'sports', 
    **/
    public static function execute($params=[]): string{
        $possibleParams = ['sports'];
        $possibleParams_str = implode(', ', $possibleParams);
        if(count($params) != 1){
            $msg = "PARAMETER MISSING in g5\\transform\\newalch\\ertel4391.execute(\$params)\n"
                . "Possible parameters ; " . $possibleParams_str;
            return $msg;
        }
        switch($params[0]){
        	case 'sports' :
        	    return self::extract_sports();
        	break;
            default:
            $msg = "INVALID PARAMETER in g5\\transform\\newalch\\ertel4391.execute(\$params)\n"
                . "Possible parameters : " . $possibleParams_str;
            return $msg;
            break;
        }
    }
    
    
    // ******************************************************
    /**
        Lists the sport codes present in the csv file.
        @return Associative array ; key = sport code ; value = I or G
        Execution result :
        Incoherent association sport / IG, line Cachemire Jacques : BASK I
        Incoherent association sport / IG, line David Wilfried : CYCL G
        Incoherent association sport / IG, line Frey Andre : FOOT I
        Incoherent association sport / IG, line Windal Claude : HOCK I
        Array
        (
            [0 BA] => G
            [0 BO] => I
            [0 CY] => I
            [0 FO] => G
            [0 PE] => G
            [0 RU] => G
            [0 SK] => I
            [AIR] => I
            [AIRP] => I
            [ALPI] => I
            [AUT] => I
            [AUTO] => I
            [AVIR] => I
            [BADM] => I
            [BAS] => G
            [BASE] => G
            [BASK] => G
            [BIL] => I
            [BILL] => I
            [BOBS] => I
            [BOWL] => I
            [BOX] => I
            [BOXI] => I
            [CANO] => I
            [CYC] => I
            [CYCL] => I
            [FEN] => I
            [FENC] => I
            [FO] => G
            [FOO] => G
            [FOOT] => G
            [GOLF] => I
            [GYMN] => I
            [HAN] => I
            [HAND] => G
            [HOC] => G
            [HOCK] => G
            [HOR] => I
            [HORS] => I
            [ICES] => I
            [JUDO] => I
            [MOTO] => I
            [PEL] => G
            [PELO] => G
            [RODE] => I
            [ROL] => I
            [ROLL] => I
            [ROW] => I
            [ROWI] => I
            [RUG] => G
            [RUGB] => G
            [SHO] => I
            [SHOO] => I
            [SKI] => I
            [SKII] => I
            [SW] => I
            [SWI] => I
            [SWIM] => I
            [TEN] => I
            [TENN] => I
            [TRA] => I
            [TRAC] => I
            [TRAV] => I
            [VOLL] => G
            [WALK] => I
            [WEI] => I
            [WEIG] => I
            [WRE] => I
            [WRES] => I
            [YAC] => I
            [YACH] => I
        )
    **/
    public static function extract_sports(){
        $rows = \lib::csvAssociative(Config::$data['dirs']['5-newalch-csv'] . DS . Ertel4391::CSV_FILE);
        $res = []; // assoc array sport => IG
        foreach($rows as $row){
            if(!isset($res[$row['SPORT']])){
                $res[$row['SPORT']] = $row['IG'];
            }
            else{
                if($res[$row['SPORT']] != $row['IG']){
                    echo "Incoherent association sport / IG, line " . $row['F_NAME'] . ' ' . $row['G_NAME']
                        . ' : ' . $row['SPORT'] . ' ' . $row['IG'] . "\n";
                }
            }
        }
        ksort($res);
        return print_r($res, true);
    }
    
}// end class    
