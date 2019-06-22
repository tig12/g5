<?php
/********************************************************************************
    Code analyzing files of 3-g55-edited
    and modifying files of 5-cura-csv/ using files of 3-g55-edited
    
    Example of use : php run-g5.php g55 570SPO g552csv date

    
    @pre        5-cura-csv/A1.csv must exist in its best possible state.
                So src/transform/cura/A/raw2csv.php must have been executed before.
                   src/transform/cura/A/ertel2csv.php must have been executed before.
                   src/transform/cura/A/legalTime.php must have been executed before.
    @pre        3-g55-edited/570SPO.csv must exist.
    
    To add a new function : 
        - add entry in POSSIBLE_PARAMS
        - implement a method named "execute_<entry>"
    
    @license    GPL
    @history    2019-05-26 00:33:22+02:00, Thierry Graff : addition to new structure
********************************************************************************/
namespace g5\transform\g55\all;

use g5\Config;
use g5\patterns\Command;
use g5\transform\g55\G55;
use tiglib\arrays\csvAssociative;

class g552csv implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 examine eminence
    **/
    const POSSIBLE_PARAMS = [
        'date',
        'name',
        'nocura',
    ];
    
    // *****************************************
    /** 
        @param $params Array containing 2 elements :
                       - the group to analyze (like '570SPO')
                       - the name of this command ("execute", useless here)
                       - the name of the action to perform
        @return report
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        $msg = "PARAMETER MISSING in g5\\transform\\cura\\A\\g552csv\n"
             . "Possible values for parameter : $possibleParams_str\n";
        if(count($params) != 3){
            return $msg;
        }
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return $msg;
        }
        $method = 'execute_' . $param;
        
        return self::$method($params[0]);
        
    }
    
    // ******************************************************
    /**
        Auxiliary function
        @return    An array containing the edited file in 3-g55-edited/
    **/
    private static function loadG55($file){
        return csvAssociative::compute(Config::$data['dirs']['3-g55-edited'] . DS . $file . '.csv', G55::CSV_SEP_LIBREOFFICE);
    }

    
    // ******************************************************
    /**
        Auxiliary function
        @return Array with 3 elements :
                - A string identifying the cura file correspônding to the G55 group (like 'A1')
                - An assoc. array containing the G55 data ; keys = cura ids
                - An assoc. array containing the cura data ; keys = cura ids
    **/
    private static function prepare_analysis($g55file){
        $g55Rows1 = self::loadG55($g55file);
        
        // find the corresponding cura file
        // For a given G55 group, there is only one cura file
        // It corresponds to the first origin different from 'G55'
        foreach($g55Rows1 as $row){
            if($row['ORIGIN'] != 'G55'){
                $origin = $row['ORIGIN'];
                break;
            }
        }
        
        $g55Rows = [];
        foreach($g55Rows1 as $row){
            if($row['ORIGIN'] != $origin){
                continue;
            }
            $g55Rows[$row['NUM']] = $row;
        }
        
        $curaRows1 = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $origin . '.csv');
        $curaRows = [];
        foreach($curaRows1 as $row){
            if(isset($g55Rows[$row['NUM']])){
                $curaRows[$row['NUM']] = $row;
            }
        }
        return [$origin, $g55Rows, $curaRows];;
    }
    
    
    
    // ******************************************************
    /**
        Lists the records that are not present in cura file.
        This function is only informative, does not participate to any build process.
        In files of 3-g55-edited, these records have their ORIGIN field set to 'G55'.
    **/
    private static function execute_nocura($g55File): string{
        $res = '';
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "    <tr><th>FAMILY</th><th>GIVEN</th><th>DAY</th><th>HOUR</th><th>PLACE</th><th>C2</th><th>CY</th><th>OCCU</th><th>NOTES</th>\n";
        $g55rows = self::loadG55($g55File);
        foreach($g55rows as $row){
            if($row['ORIGIN'] == 'G55'){
                // all informations coming from paper book are stored in _55 columns
                $res .= "    <tr>\n";
                $res .= "        <td>{$row['FAMILY_55']}</td>\n";
                $res .= "        <td>{$row['GIVEN_55']}</td>\n";
                $res .= "        <td>{$row['DAY_55']}</td>\n";
                $res .= "        <td>{$row['HOUR_55']}</td>\n";
                $res .= "        <td>{$row['PLACE_55']}</td>\n";
                $res .= "        <td>{$row['C2_55']}</td>\n";
                $res .= "        <td>{$row['CY_55']}</td>\n";
                $res .= "        <td>{$row['OCCU_55']}</td>\n";
                $res .= "        <td>{$row['NOTES_55']}</td>\n";                                    
                $res .= "    <tr>\n";
            }
        }
        $res .= "</table>\n";
        return $res;
    }
    
    
    // ******************************************************
    /**
        Echoes the lines where G55 name is different from cura.
    **/
    private static function execute_name($g55File){
        
        [$origin, $g55Rows, $curaRows] = self::prepare_analysis($g55File);
        $n = 0;
        foreach($g55Rows as $NUM => $g55Row){
            $fnameCura = $curaRows[$NUM]['FNAME'];
            $fname55 = $g55Row['FAMILY_55'];
            $gnameCura = $curaRows[$NUM]['GNAME'];
            $gname55 = $g55Row['GIVEN_55'];
            if(
                ($fname55 != '' && $fname55 != $fnameCura)
             || ($gname55 != '' && $gname55 != $gnameCura)
            ){
                echo "$NUM $fnameCura * $gnameCura \t| $fname55 * $gname55\n";
                $n++;
            }
        }
        echo "N = $n\n";
    }
    
    
    // ******************************************************
    /**
        Echoes the lines where G55 date (day or time) is different from cura.
    **/
    private static function execute_date($g55File){
        
        [$origin, $g55Rows, $curaRows] = self::prepare_analysis($g55File);
        $n = 0;
        foreach($g55Rows as $NUM => $g55Row){
            $dateCura = $curaRows[$NUM]['DATE'];
            $dateCura_c = $curaRows[$NUM]['DATE_C'];
            $day55 = $g55Row['DAY_55'];
            $hour55 = $g55Row['HOUR_55'];
            if($day55 != '' || $hour55 != ''){
                echo "$NUM $dateCura * $dateCura_c \t| $day55 * $hour55\n";
                $n++;
            }
        }
        echo "N = $n\n";
    }
    
    
}// end class
