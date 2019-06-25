<?php
/********************************************************************************
    Code analyzing files of 3-g55-edited
    and modifying files of 5-cura-csv/ using files of 3-g55-edited
    
    Example of use : php run-g5.php g55 570SPO edited2cura date

    
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

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\transform\g55\G55;
use tiglib\arrays\csvAssociative;

class edited2cura implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 examine eminence
    **/
    const POSSIBLE_PARAMS = [
        'date',
        'name',
        'nocura',
        'occupation',
        'place',
    ];
    
    // *****************************************
    /** 
        @param $params Array containing 3 or more strings :
                       - the group to analyze (like '570SPO')
                         this parameter has already been checked in class G5::Run
                       - the name of this command ("execute", useless here)
                       - the name of the action to perform
                       - other parameters transmitted to the function implementing the command.
        @return report
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) < 3){
            return  "PARAMETER MISSING in g5\\transform\\cura\\A\\edited2cura\n"
                  . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return  "INVALID PARAMETER '$param' in g5\\transform\\cura\\A\\edited2cura\n"
                  . "Possible values for parameter : $possibleParams_str\n";
        }
        $method = 'execute_' . $param;
        
        return self::$method($params);
    }
    
    // ******************************************************
    /**
        Auxiliary function
        @return    Regular array containing the edited file in 3-g55-edited/
    **/
    private static function loadG55($file){
        return csvAssociative::compute(Config::$data['dirs']['3-g55-edited'] . DS . $file . '.csv', G55::CSV_SEP_LIBREOFFICE);
    }

    // ******************************************************
    /**
        Auxiliary function
        @return    Regular array containing the cura file in 5-cura-csv/
    **/
    private static function loadCura($file){
        return csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $file . '.csv');
    }

    // ******************************************************
    /**
        Auxiliary function
        @return    Associative array containing the cura file in 5-cura-csv/ ; keys = cura ids (NUM)
    **/
    private static function loadCuraNum($file){
        $curaRows1 = self::loadCura($file);
        $res = [];
        foreach($curaRows1 as $row){
            $res[$row['NUM']] = $row;
        }
        return $res;
    }

    // ******************************************************
    /**
        Auxiliary function
        @return Array with 3 elements :
                - A string identifying the cura file correspônding to the G55 group (like 'A1')
                - An assoc. array containing the G55 data ; keys = cura ids (NUM)
                - An assoc. array containing the cura data ; keys = cura ids (NUM)
    **/
    private static function prepare($g55file){
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
        
        //$curaRows1 = csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $origin . '.csv');
        $curaRows1 = self::loadCura($origin);
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
        Action depending on the 4th parameter :
        - if "list", echoes the lines where G55 name is different from cura.
        - if "update", injects the values of G55 columns "FAMILY_55" and "GIVEN_55"
          in corresponding columns of cura file, in 5-cura-csv.
        @param  $params Array containing 4 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "name" (useless here)
                - "list" or "update"
    **/
    private static function execute_name($params): string{
        if(count($params) < 4){
            return "WRONG USAGE - This command needs an other parameter\n"
                 . "- \"list\" : lists name differences between cura and Gauquelin 55.\n"
                 . "- \"update\" : updates Cura file with Gauquelin 55 values.\n";
        }
        if(count($params) > 4){
            return "WRONG USAGE - Useless parameter \"{$params[4]}\".\n";
        }
        
        $g55File = $params[0];
        $action = $params[3];
        
        [$origin, $g55Rows, $curaRows] = self::prepare($g55File);
        
        if($action == 'update'){
            $curaFull = self::loadCuraNum($origin);
        }
        $report = '';
        $N = 0;
        foreach($g55Rows as $NUM => $g55Row){
            $fnameCura = $curaRows[$NUM]['FNAME'];
            $fname55 = $g55Row['FAMILY_55'];
            $gnameCura = $curaRows[$NUM]['GNAME'];
            $gname55 = $g55Row['GIVEN_55'];
            if(
                ($fname55 != '' && $fname55 != $fnameCura)
             || ($gname55 != '' && $gname55 != $gnameCura)
            ){
                if($action == 'list'){
                  $report .= "$NUM $fnameCura * $gnameCura \t| $fname55 * $gname55\n";
                }
                else{
                    if($fname55 != ''){
                        $curaFull[$NUM]['FNAME'] = $fname55;
                    }
                    if($gname55 != ''){
                        $curaFull[$NUM]['GNAME'] = $gname55;
                    }
                }
                $N++;
            }
        }                              
        $report .= "$N differences on names between $g55File and $origin\n";
        
        if($action == 'update'){
            $newCura = implode(G5::CSV_SEP, array_keys($curaFull[1])) . "\n";
            foreach($curaFull as $row){
                $newCura .= implode(G5::CSV_SEP, $row) . "\n";
            }
            $filename = Config::$data['dirs']['5-cura-csv'] . DS . $origin . '.csv';
            file_put_contents($filename, $newCura);
            $report .= "Differences injected in $filename\n";
        }
        
        return $report;
    }
    
    
    // ******************************************************
    /**
        Action depending on the 4th parameter :
        - if "list", echoes the lines where G55 place name is different from cura.
        - if "update", injects the values of G55 column "PLACE_55"
          in corresponding column of cura file, in 5-cura-csv.
        @param  $params Array containing 4 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "place" (useless here)
                - "list" or "update"
    **/
    private static function execute_place($params): string{
        if(count($params) < 4){
            return "WRONG USAGE - This command needs an other parameter\n"
                 . "- \"list\" : lists place name differences between cura and Gauquelin 55.\n"
                 . "- \"update\" : updates Cura file with Gauquelin 55 values.\n";
        }
        if(count($params) > 4){
            return "WRONG USAGE - Useless parameter \"{$params[4]}\".\n";
        }
        
        $g55File = $params[0];
        $action = $params[3];
        
        [$origin, $g55Rows, $curaRows] = self::prepare($g55File);
        
        if($action == 'update'){
            $curaFull = self::loadCuraNum($origin);
        }
        $report = '';
        $N = 0;
        foreach($g55Rows as $NUM => $g55Row){
            $placeCura = $curaRows[$NUM]['PLACE'];
            $place55 = $g55Row['PLACE_55'];
            if($place55 != '' && $place55 != $placeCura){
                if($action == 'list'){
                  $report .= "$NUM $placeCura \t| $place55\n";
                }
                else{
                    if($place55 != ''){
                        $curaFull[$NUM]['PLACE'] = $place55;
                    }
                }
                $N++;
            }
        }                              
        $report .= "$N differences on place name between $g55File and $origin\n";
        
        if($action == 'update'){
            $newCura = implode(G5::CSV_SEP, array_keys($curaFull[1])) . "\n";
            foreach($curaFull as $row){
                $newCura .= implode(G5::CSV_SEP, $row) . "\n";
            }
            $filename = Config::$data['dirs']['5-cura-csv'] . DS . $origin . '.csv';
            file_put_contents($filename, $newCura);
            $report .= "Differences injected in $filename\n";
        }
        
        return $report;
    }
    
    
    // ******************************************************
    /**
        Echoes the lines where G55 date (day or time) is different from cura.
    **/
    private static function execute_date($g55File): string{
// not finished
        
        [$origin, $g55Rows, $curaRows] = self::prepare($g55File);
        $N = 0;
        foreach($g55Rows as $NUM => $g55Row){
            $dateCura = $curaRows[$NUM]['DATE'];
            $dateCura_c = $curaRows[$NUM]['DATE_C'];
            $day55 = $g55Row['DAY_55'];
            $hour55 = $g55Row['HOUR_55'];
            if($day55 != '' || $hour55 != ''){
                echo "$NUM $dateCura * $dateCura_c \t| $day55 * $hour55\n";
                $N++;
            }
        }
        echo "N = $N\n";
    }
    
    
    // ******************************************************
    /**
        Lists the records that are not present in cura file.
        This function is only informative, does not participate to any build process.
        In files of 3-g55-edited, these records have their ORIGIN field set to 'G55'.
        @param  $params Array containing 3 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "nocura" (useless here)
    **/
    private static function execute_nocura($params): string{
        if(count($params) > 3){
            return "WRONG USAGE - Useless parameter \"{$params[3]}\".\n";
        }
        $g55File = $params[0];
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
        Lists the records where occupation codes in G55 groups differ from Cura groups.
        This function is only informative, does not participate to any build process.
        @param  $params Array containing 3 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "occupation" (useless here)
    **/
    private static function execute_occupation($params): string{
        if(count($params) > 3){
            return "WRONG USAGE - Useless parameter \"{$params[3]}\".\n";
        }
        $g55File = $params[0];
        $res = '';
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "    <tr><th>NUM</th><th>FAMILY</th><th>GIVEN</th><th>DATE</th><th>PLACE</th><th>OCCU<br>G55</th><th>OCCU<br>Cura</th>\n";
        [$origin, $g55Rows, $curaRows] = self::prepare($g55File);
        $N = 0;
        foreach($g55Rows as $g55Row){
            $NUM = $g55Row['NUM'];
            if($g55Row['OCCU_55'] != ''){
                $res .= "    <tr>\n";
                $res .= "        <td>$NUM</td>\n";
                $res .= "        <td>{$curaRows[$NUM]['FNAME']}</td>\n";
                $res .= "        <td>{$curaRows[$NUM]['GNAME']}</td>\n";
                $res .= "        <td>{$curaRows[$NUM]['DATE']}</td>\n";
                $res .= "        <td>{$curaRows[$NUM]['PLACE']}</td>\n";
                $res .= "        <td>{$curaRows[$NUM]['OCCU']}</td>\n";
                $res .= "        <td>{$g55Row['OCCU_55']}</td>\n";
                $res .= "    <tr>\n";
                $N++;
            }
        }
        $res .= "</table>\n";
        $res .= "$N different occupation codes\n";
        return $res;
        
    }
    
    
}// end class
