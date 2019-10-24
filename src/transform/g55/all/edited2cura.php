<?php
/********************************************************************************
    Code modifying files of 5-cura-csv/ using files of 3-g55-edited/
    
    Example of use : php run-g5.php g55 570SPO edited2cura date
    
    @pre        5-cura-csv/A1.csv must exist in its best possible state.
                To be clean, execute before
                php run-g5.php cura A1 all
                
    @pre        3-g55-edited/570SPO.csv must exist.
    
    To add a new function : 
        - add entry in POSSIBLE_PARAMS
        - implement a method named "execute_<entry>"
    
    @license    GPL
    @history    2019-05-26 00:33:22+02:00, Thierry Graff : addition to new structure
********************************************************************************/
namespace g5\transform\g55\all;

use g5\Config;
use g5\G5;
use g5\transform\cura\Cura;
use g5\transform\g55\G55;
use g5\patterns\Command;
use g5\model\Libreoffice;

class edited2cura implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 examine eminence
    **/
    const POSSIBLE_PARAMS = [
        'date',
        'name',
        'nocura',
        'occu',
        'place',
    ];
    
    // *****************************************
    /** 
        @param $params Array containing 3 or more strings :
                       - the group to process (like '570SPO')
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
        
        $g55group = $params[0];
        $action = $params[3];
        
        if(!G55::editedFileExists($g55group)){
            return "Cannot compute $g55group because " . G55::editedFilename($g55group) . " does not exist\n";
        }
        
        [$origin, $g55rows, $curarows] = G55::prepareCuraMatch($g55group);
        
        if($action == 'update'){
            $curaFull = Cura::loadTmpCsv_num($origin);
        }
        $report = '';
        $N = 0;
        foreach($g55rows as $NUM => $g55row){
            $fnameCura = $curarows[$NUM]['FNAME'];
            $fname55 = $g55row['FAMILY_55'];
            $gnameCura = $curarows[$NUM]['GNAME'];
            $gname55 = $g55row['GIVEN_55'];
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
        $report .= "$N differences on names between $g55group and $origin\n";
        
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
        
        $g55group = $params[0];
        $action = $params[3];
        
        [$origin, $g55rows, $curarows] = G55::prepareCuraMatch($g55group);
        
        if($action == 'update'){
            $curaFull = Cura::loadTmpCsv_num($origin);
        }
        $report = '';
        $N = 0;
        foreach($g55rows as $NUM => $g55row){
            $placeCura = $curarows[$NUM]['PLACE'];
            $place55 = $g55row['PLACE_55'];
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
        $report .= "$N differences on place name between $g55group and $origin\n";
        
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
        Informative only
        @param  $params Array containing 3 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "date" (useless here)
        Results : real differences in hour between cura and g55 for :
                                         cura  | g55
        688 BOX 1904-05-29 Thil Marcel : 22:00 | 07:00
        4 different days :
        36 ATH El Mabrouk Mohamed : 1928-10-27 | 1928-10-17
        595 BOX Cuillieres René :   1929-07-22 | 1929-07-12
        1976 RUG Puig-Aubert Henri  1924-03-24 | 1925-03-24
        1999 RUG Terreau Maurice : 1923-01-03  | 1923-01-30
        
    **/
    private static function execute_date($params): string{
        if(count($params) > 3){
            return "WRONG USAGE - Useless parameter \"{$params[3]}\".\n";
        }
        
        $g55group = $params[0];
        [$origin, $g55rows, $curarows] = G55::prepareCuraMatch($g55group);
                                                                                  
        $report_hour = $report_day = '';
        $N_hour = $N_day = 0;
        foreach($g55rows as $NUM => $g55row){
            $name = $curarows[$NUM]['FNAME'] . ' ' . $curarows[$NUM]['GNAME'];
            $occu = $curarows[$NUM]['OCCU'];
            $compare_cura = $curarows[$NUM]['DATE_C'] == '' ? $curarows[$NUM]['DATE'] : $curarows[$NUM]['DATE_C'];
            $day_cura = substr($compare_cura, 0, 10);
            $hour_cura = substr($compare_cura, 11, 5); // hour without timezone info
            $day55 = $g55row['DAY_55'];
            $hour55 = Libreoffice::fix_hour($g55row['HOUR_55']);
            /*
            $hour55 = $g55row['HOUR_55'];
            if(is_numeric($hour55)){
                $hour55 = str_pad ($hour55 , 2, '0', STR_PAD_LEFT) . ':00';
            }
            if(strlen($hour55) == 8){
                $hour55 = substr($hour55, 0, 5); // remove seconds (:00) added by libreoffice
            }                                                                                                                       
            */
            if($day55 != ''){
                $report_day .= "$NUM $occu $name : $day_cura | $day55\n";
                $N_day++;
            }
            if($hour55 != '' && $hour55 != $hour_cura){
                $report_hour .= "$NUM $occu $day_cura $name : $hour_cura | $hour55\n";
                $N_hour++;
            }
        }
        return "$N_hour different hours :\n" . $report_hour
             . "$N_day different days :\n" . $report_day;
    }
    
    
    // ******************************************************
    /**
        Lists the records that are not present in cura file.
        Informative only
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
        $g55group = $params[0];
        $res = '';
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "    <tr><th>FAMILY</th><th>GIVEN</th><th>DAY</th><th>HOUR</th><th>PLACE</th><th>CY</th><th>C2</th><th>OCCU</th><th>NOTES</th>\n";
        $g55rows = G55::loadG55Edited($g55group);
        foreach($g55rows as $row){
            if($row['ORIGIN'] == 'G55'){
                // all informations coming from paper book are stored in _55 columns
                $res .= "    <tr>\n";
                $res .= "        <td>{$row['FAMILY_55']}</td>\n";
                $res .= "        <td>{$row['GIVEN_55']}</td>\n";
                $res .= "        <td>{$row['DAY_55']}</td>\n";
                $res .= "        <td>{$row['HOUR_55']}</td>\n";
                $res .= "        <td>{$row['PLACE_55']}</td>\n";
                $res .= "        <td>{$row['CY_55']}</td>\n";
                $res .= "        <td>{$row['C2_55']}</td>\n";
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
        Informative only
        @param  $params Array containing 3 strings transmitted by execute() :
                - The G55 file to process
                - "edited2cura" (useless here)
                - "occupation" (useless here)
    **/
    private static function execute_occu($params): string{
        if(count($params) > 3){
            return "WRONG USAGE - Useless parameter \"{$params[3]}\".\n";
        }
        $g55group = $params[0];
        $res = '';
        $res .= "<table class=\"wikitable margin\">\n";
        $res .= "    <tr><th>NUM</th><th>FAMILY</th><th>GIVEN</th><th>DATE</th><th>PLACE</th><th>OCCU<br>Cura</th><th>OCCU<br>G55</th>\n";
        [$origin, $g55rows, $curarows] = G55::prepareCuraMatch($g55group);
        $N = 0;
        foreach($g55rows as $g55row){
            $NUM = $g55row['NUM'];
            if($g55row['OCCU_55'] != ''){
                $res .= '    ' . ($g55row['OCCU_55'] == 'FEM' ? '<tr>' : '<tr class="bold">') . "\n";
                $res .= "        <td>$NUM</td>\n";
                $res .= "        <td>{$curarows[$NUM]['FNAME']}</td>\n";
                $res .= "        <td>{$curarows[$NUM]['GNAME']}</td>\n";
                $res .= "        <td>{$curarows[$NUM]['DATE']}</td>\n";
                $res .= "        <td>{$curarows[$NUM]['PLACE']}</td>\n";
                $res .= "        <td>{$curarows[$NUM]['OCCU']}</td>\n";
                $res .= "        <td>{$g55row['OCCU_55']}</td>\n";
                $res .= "    <tr>\n";
                $N++;
            }
        }
        $res .= "</table>\n";
        $res .= "$N different occupation codes\n";
        return $res;
    }
    
    
}// end class
