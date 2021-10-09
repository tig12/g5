<?php
/********************************************************************************
    Generates the files in 5-tmp/g55-generated/
    from files in
    - 3-edited/cura-marked/
    and
    - 5-tmp/cura-csv/
    Takes an exact copy of files in 5-tmp/cura-csv/
    Uses files from 3-edited/cura-marked/ to filter and dispatch in different resulting files in g55-generated
    Adds a column ORIGIN

    Called by : php run-g5.php g55 <filenamme> marked2generated
    <filename> can be one of G55::DATAFILES_POSSIBLES ; for example '570SPO'
    
    @license    GPL
    @history    2019-05-26 00:51:46+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\g55\all;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\g55\G55;
use tiglib\arrays\sortByKey;

class marked2generated implements Command {
    
    // *****************************************
    /** 
        @param $params Array containing 2 elements :
                       - the group to generate (like '570SPO')
                       - the name of this command (useless here)
        @return report
    **/
    public static function execute($params=[]): string{
        
        // no check on $params because this is done in G55Command
        
        $report = '';
        
        $groupCode = $params[0];
        
        $cura_file = G55::GROUPS[$groupCode][1]; // A1, A2 etc.
        $file_marked = Config::$data['dirs']['3-cura-marked'] . DS . $cura_file . '.csv';
        $file_csv = Config::$data['dirs']['5-cura-csv'] . DS . $cura_file . '.csv';
        $file_output = Config::$data['dirs']['5-g55-generated'] . DS . $groupCode . '.csv';

        $report .= "Generating $file_output";
        
        $marked = self::loadMarked($file_marked, $groupCode);
        if($marked === false){
            $report .=  "$groupCode not marked yet - File not generated\n";
            return $report;
        }
        
        $res = [];
        
        $input = file($file_csv);
        $N = count($input);
        $fieldnames = explode(G5::CSV_SEP, $input[0]);
        for($i=1; $i < $N; $i++){
            $fields = explode(G5::CSV_SEP, $input[$i]);
            $NUM = $fields[0]; // by convention, all generated csv file of 5-cura-csv have NUM as first field
            if(in_array($NUM, $marked)){
                $res[] = $fields;
            }
        }
        //
        // sort $res
        //
        // $sort_field : necessary because "570 sportif" is listed by sport in Gauquelin book
        // Other fields are sorted alphabetically
        // $sort_field permits to have a convenient list to compare with the book.
        // here simplification : files in 5-tmp/cura-csv/
        // have first field = NUM and second field = FNAME
        $sort_field = ($groupCode == '570SPO' ? 0 : 1);
        $res = sortByKey::compute($res, $sort_field);
        $report .= ' - ' . count($res) . " persons stored\n";
        // generate output
        $output = 'ORIGIN' . G5::CSV_SEP . $input[0]; // field names
        foreach($res as $fields){
            $output .= $cura_file . G5::CSV_SEP . implode(G5::CSV_SEP, $fields);
        }
        file_put_contents($file_output, $output);
        return $report;
    }
    
    
    // ******************************************************
    /**
        Loads one csv file located in 3-edited/cura-marked/
        Auxiliary of self::execute()
        @param $filename    String file name located in 3-edited/cura-marked/
        @param $groupCode   String identifying a Gauquelin 1955 group, like "570SPO"
        @return false or a regular array containing the NUM of marked records
    **/
    private static function loadMarked($filename, $groupCode){
        $res = [];
        if(!is_file($filename)){
            return false;
        }
        $raw = file_get_contents($filename);
        $raw = str_replace('"', '', $raw); // libreoffice adds " and I don't know how to remove them
        $lines = explode("\n", $raw);
        $nlines = count($lines);
        $fieldnames = explode(G55::CSV_SEP_LIBREOFFICE, $lines[0]);
        $flip = array_flip($fieldnames);
        for($i=1; $i < $nlines; $i++){
            if(trim($lines[$i]) == ''){
                continue;
            }
            $fields = explode(G55::CSV_SEP_LIBREOFFICE, $lines[$i]);
            $code = $fields[$flip['1955']];
            if($code != $groupCode){
                continue;
            }
            $res[] = $fields[$flip['NUM']];
        }
        return $res;
    }
    
}// end class
