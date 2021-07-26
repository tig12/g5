<?php
/********************************************************************************
    Builds a list of 229 mathematicians ranked by eminence.
    Data source : book
        Histoire des mathématiques
        Jean-Pierre Escofier
        2008
        Ed. Dunod
        Collection Les topos
        
        Uses the index, pp 125 - 128 of the book
    
    @license    GPL
    @history    2020-11-28 04:20:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\eminence\math;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\{Source, Group};
use tiglib\arrays\sortByKey;

// *****************************************
//          Model class
// *****************************************
class EscofierModel {

    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION = 'eminence' . DS . 'math' . DS . 'escofier.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'escofier';
    
    // *********************** Source management ***********************
    
    /** @return a Source object for the raw file **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** @return Path to the raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'eminence', 'math', 'escofier.txt']);
    }
    
    /** Loads raw file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
    
    // *********************** Tmp file manipulation ***********************
    
    /** @return Path to the csv file stored in data/tmp/ **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'eminence', 'math', 'escofier.csv']);
    }
    
}


// *****************************************
//          Implementation of Command
// *****************************************
class escofier implements Command {
    
    /** Possible values of the command **/
    const POSSIBLE_PARAMS = [
        'raw2tmp',
    ];
    
    /** 
        @param  $params
                    - $params[0] contains the name of the action (ex raw2tmp)
                    - Other params are passed to the exec_* method
        @return String report
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        
        $method = 'exec_' . $param;
        
        if(count($params) > 1){
            array_shift($params);
            return self::$method($params);
        }
        
        return self::$method();
    }
    
    // ******************************************************
    /** 
        Input
            data/raw/eminence/maths/escofier.txt
        Output
            data/tmp/eminence/math/escofier.csv
    **/
    private static function exec_raw2tmp(){
        $report =  "--- pdd raw2tmp ---\n";
        $lines = EscofierModel::loadRawFile();
        $N = 0;
        $p = '/(.*?)\s*:\s*(.*)/';
        $res = [];
        foreach($lines as $line){
            $line = trim($line);
            if($line == ''){
                continue;
            }
            $N++;
            if(!preg_match($p, $line, $m)){
                echo "NO MATCH === $line\n";
            }
            $pages = self::computePages($m[2]);          
            // family given names
            if($m[1] == 'Al Haytham'){
                $fname = 'Al Haytham';
                $gname = '';
            }
            else if($m[1] == 'Al Kashi Ghiyath'){
                $fname = 'Al Kashi';
                $gname = 'Ghiyath';
            }
            else if($m[1] == 'Al Khazin'){
                $fname = 'Al Khazin';
                $gname = '';
            }
            else if($m[1] == 'Al Khwarizmi Mohammed'){
                $fname = 'Al Khwarizmi';                                        
                $gname = 'Mohammed';
            }
            else if($m[1] == 'Al Tusi Nasir al Din'){
                $fname = 'Al Tusi al Din';
                $gname = 'Nasir';
            }
            else if($m[1] == 'Ferro Scipione del'){
                $fname = 'del Ferro';
                $gname = 'Scipione';
            }
            else if($m[1] == 'Van der Waerden Bartel'){
                $fname = 'Van der Waerden';
                $gname = 'Bartel';
            }
            else if($m[1] == 'Piero della Francesca'){
                $fname = 'Piero della Francesca';
                $gname = '';
            }
            else if($m[1] == 'Peletier du Mans Jacques'){
                $fname = 'Peletier du Mans';
                $gname = 'Jacques';
            }
            else if($m[1] == 'Philippe de Macédoine'){
                $fname = 'Philippe de Macédoine';
                $gname = '';
            }
            else if($m[1] == 'La Condamine Charles Marie de'){
                $fname = 'de La Condamine';
                $gname = 'Charles Marie';
            }
            else if($m[1] == 'Ibn Sahl'){
                $fname = 'Ibn Sahl';
                $gname = '';
            }
            else if($m[1] == 'Mac Lane Saunders'){
                $fname = 'Mac Lane';
                $gname = 'Saunders';
            }
            else if($m[1] == 'Le Verrier Urbain'){
                $fname = 'Le Verrier';
                $gname = 'Urbain';
            }
            else if($m[1] == 'Von Neumann John'){
                $fname = 'Von Neumann';
                $gname = 'John';
            }
            else{
                // general case
                $pos = strpos($m[1], ' ');
                if($pos === false){
                    $fname = $m[1];
                    $gname = '';
                }
                else{
                    $fname = substr($m[1], 0, $pos);
                    $gname = substr($m[1], $pos+1);
                }
            }
            $res[] = [
                'FNAME' => $fname,
                'GNAME' => $gname,
                'SCORE' => count($pages),
                'PAGES' => $pages,
            ];
        }
        $res = sortByKey::compute($res, 'SCORE');
        //
        $res2 = implode(G5::CSV_SEP, ['FNAME', 'GNAME', 'SCORE', 'PAGES']) . "\n";
        for($i=count($res)-1; $i >= 0; $i--){
            $cur = $res[$i];
            $cur['PAGES'] = implode('+', $cur['PAGES']);
            $res2 .= implode(G5::CSV_SEP, $cur) . "\n";
        }
        //
        $outfile = EscofierModel::tmpFilename();
        file_put_contents($outfile, $res2);
        $report .= "Wrote $N records in $outfile\n";
        return $report;
    }
    
    /**
        Auxiliary of exec_raw2tmp()
        @param  $str    List of pages as found in raw file
                        Examples :
                        296
                        108, 109
        @return Array of page numbers
    **/
    public static function computePages($str){
        $parts = explode(', ', $str);
        $res = [];
        foreach($parts as $part){
            $part = trim($part);
            $res[] = $part;
        }
        return $res;
    }
    
}// end class
