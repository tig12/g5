<?php
/********************************************************************************
    Builds a list of 303 mathematicians ranked by eminence.
    Data source : book
        Histoires de problèmes. Histoire des mathématiques.
        Commission Inter-IREM Epistémologie et Histoire des Mathématiques
        Ed. Ellipses Paris
        Collection : IREM - Epistémologie et Histoire des Maths
        1993
        
        Uses the "Index des noms", pp 415 - 419 of the book
    
    @license    GPL
    @history    2020-11-27 13:13:07+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\eminence\math;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\{Source, Group};
use tiglib\arrays\sortByKey;

// *****************************************
//          Model class
// *****************************************
class IremModel {
// PeifferDahanDalmedico

    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION = 'eminence' . DS . 'math' . DS . 'irem.yml';
                                                                                 
    /** Slug of the group in db **/
    const GROUP_SLUG = 'irem';
    
    // *********************** Source management ***********************
    
    /** @return a Source object for the raw file **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['db'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** @return Path to the raw file **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'eminence', 'math', 'irem.txt']);
    }
    
    /** Loads raw file in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
    
    // *********************** Tmp file manipulation ***********************
    
    /** @return Path to the csv file stored in data/tmp/ **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'eminence', 'math', 'irem.csv']);
    }
    
}


// *****************************************
//          Implementation of Command
// *****************************************
class irem implements Command {
    
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
            data/raw/eminence/maths/irem.txt
        Output
            data/tmp/eminence/math/irem.csv
    **/
    private static function exec_raw2tmp(){
        $report =  "--- irem raw2tmp ---\n";
        $lines = IremModel::loadRawFile();
        $N = 0;
        $res = [];
        foreach($lines as $line){
            $line = trim($line);
            if($line == ''){
                continue;
            }
            if(strpos($line, ' voir ') !== false){
                //echo "SKIP $line\n";
                // see README in data/raw/eminence/math/irem
                continue;
            }
            $pos = strpos($line, ')');
            if($pos === false){
                $pos = strpos($line, ',');
                $name = substr($line, 0, $pos);
                $strPages = trim(substr($line, $pos+1));
            }
            else{
                $name = substr($line, 0, $pos+1); // +1 to catch ')'
                $strPages = trim(substr($line, $pos+2)); // +2 to remove ','
            }
            //
            $pages = self::computePages($strPages);
            // separate family, given names and dates
            $fname = '';
            $gname = '';
            $dates = '';
            if($name == 'PIERO DELLA FRANCESCA (1416?-1492)'){
                $fname = 'PIERO DELLA FRANCESCA';
                $gname = '';
                $dates = '1416?-1492';
            }
            else if($name == 'IBN AL-KHAYYAM Omar, (vers 1048 - vers 1138)'){
                $fname = 'IBN AL-KHAYYAM';
                $gname = 'Omar';
                $dates = 'vers 1048 - vers 1138';
            }
            else if($name == 'NASIR AL-DIN AL-TUSI, (1201-1274)'){
                $fname = 'NASIR AL-DIN AL-TUSI';
                $gname = '';
                $dates = '1201-1274';
            }
            else if($name == 'THABIT IBN QURRA, (836-901)'){
                $fname = 'THABIT IBN QURRA';
                $gname = '';
                $dates = '836-901';
            }
            else if($name == 'LEONARD DE VINCI, (1452-1519)'){
                $fname = 'DE VINCI';
                $gname = 'Leonard';
                $dates = '1452-1519';
            }
            else if($name == 'AL-KHW ARIZMI ibn Musa (vers 780 - vers 850)'){
                $fname = 'AL-KHW ARIZMI';
                $gname = 'ibn Musa';
                $dates = 'vers 780 - vers 850';
            }
            else if($name == 'IBN AL-HAYTHAM dit ALHAZEN (965 - vers 1040)'){
                $fname = 'IBN AL-HAYTHAM';
                $gname = 'dit ALHAZEN';
                $dates = '965 - vers 1040';
            }
            else if($name == 'IBN FZRA ABRAHAM BEN MEIR (vers 1090 - vers 1164)'){
                $fname = 'IBN FZRA ABRAHAM BEN MEIR';
                $gname = '';
                $dates = 'vers 1090 - vers 1164';
            }
            else if($name == 'FRENICLE DE BESSY Bernard, (1605-1675)'){
                $fname = 'FRENICLE DE BESSY';
                $gname = 'Bernard';
                $dates = '1605-1675';
            }
            else if($name == 'VREDEMAN DE VRIES Hans, (1527-1604)'){
                $fname = 'VREDEMAN DE VRIES';
                $gname = 'Hans';
                $dates = '1527-1604';
            }
            else if($name == 'TYCHO BRAHE, (1546-1601)'){
                $fname = 'BRAHE';
                $gname = 'Tycho';
                $dates = '1546-1601';  
            }
            else if($name == 'VAN DOESBURG Theo, (1883-1931)'){
                $fname = 'VAN DOESBURG';
                $gname = 'Theo';
                $dates = '1883-1931';
            }
            else if($name == 'VAN EESTEREN Comelis, (né en 1897)'){
                $fname = 'VAN EESTEREN';
                $gname = 'Comelis';
                $dates = 'né en 1897';
            }
            else if($name == 'VAN EYCK Jean, (vers 1390-1441)'){
                $fname = 'VAN EYCK';
                $gname = 'Jean';
                $dates = 'vers 1390-1441';
            }
            else if($name == 'SAINT VINCENT Grégoire, de (1584-1667)'){
                $fname = 'SAINT VINCENT';
                $gname = 'Grégoire, de';
                $dates = '1584-1667';
            }
            else if($name == 'DE GUA Jean Paul, (1712-1786)'){
                $fname = 'DE GUA';
                $gname = 'Jean Paul';
                $dates = '1712-1786';                                                                               
            }
            else if($name == 'DE L\'ORME Philibert, (1514-1570)'){
                $fname = 'DE L\'ORME';
                $gname = 'Philibert';
                $dates = '1514-1570';
            }
            else if($name == 'DE LA CHAMBRE'){
                $fname = 'DE LA CHAMBRE';
                $gname = '';
                $dates = '';
            }
            else if($name == 'DE LA VALLEE POUSSIN Charles, (1866- 1962)'){
                $fname = 'DE LA VALLEE POUSSIN ';
                $gname = 'Charles';
                $dates = '1866- 1962';
            }
            else if($name == 'LA CONDAMINE Charles-Marie, de (1701-1774)'){
                $fname = 'LA CONDAMINE';
                $gname = 'Charles-Marie, de';
                $dates = '1701-1774';
            }
            else if($name == 'KAMAL AL-DIN AL-FARISI (?-1320)'){
                $fname = 'KAMAL AL-DIN AL-FARISI';
                $gname = '';
                $dates = '?-1320';
            }
            else if($name == 'IBN FALLUS Abu-Tahit, (1194-1252)'){
                $fname = 'IBN FALLUS';
                $gname = 'Abu-Tahit';
                $dates = '1194-1252';
            }
            else if($name == 'DI PAOLO Giovanni, (1399-1482)'){
                $fname = 'DI PAOLO';
                $gname = 'Giovanni';
                $dates = '1399-1482';
            }
            else{
                // general case
                preg_match('/((?:\p{Lu}|\-|\')+)\s*(.*)/u', $name, $m);
                $fname = $m[1];
                $pos1 = strpos($m[2], '(');
                $pos2 = strpos($m[2], ')');
                if($pos1 !== false && $pos2 !== false){
                    $gname = substr($m[2], 0, $pos1);
                    $gname = trim(str_replace(',', '', $gname));
                    $dates = substr($m[2], $pos1+1, $pos2-$pos1-1);
                }
                else{
                    $gname = $m[2];
                }
            }
            $N++;
            $res[] = [
                'FNAME' => ucFirst(strTolower($fname)),
                'GNAME' => $gname,
                'DATES' => $dates,
                'SCORE' => count($pages),
                'PAGES' => implode('+', $pages),
            ];
        }
        //
        $res = sortByKey::compute($res, 'SCORE');
        //
        $res2 = implode(G5::CSV_SEP, ['FNAME', 'GNAME', 'DATES', 'SCORE', 'PAGES']) . "\n";
        for($i=count($res)-1; $i >= 0; $i--){
            $res2 .= implode(G5::CSV_SEP, $res[$i]) . "\n";
        }
        //
        $outfile = IremModel::tmpFilename();
        file_put_contents($outfile, $res2);
        $report .= "Wrote $N records in $outfile\n";
        return $report;
    }
    
    /**
        Auxiliary of exec_raw2tmp()
        @param  $str    Examples :
                        117, 225, 232, 271, 274
                        75,82,83,109, 121, 126-128, 132-134,
        @return Array of page numbers
    **/
    public static function computePages($str){
        $parts = explode(',', $str);
        $res = [];
        foreach($parts as $part){
            $part = trim($part);
            if($part == ''){
                continue;
            }
            if(is_numeric($part) || $part == 'XIV'){
                // single page number
                // cast to solve bug (a dot sometimes remains ar the end) - not understood
                $res[] = (int)$part;
                continue;
            }
            // page range, like 174-176
            $tmp = explode('-', $part);
            if(count($tmp) != 2){
                echo "ERROR in computePages($str) : $part\n";
                continue;
            }
            [$p1, $p2] = $tmp;
            // cast to solve bug (a dot sometimes remains ar the end) - not understood
            $p1 = (int)$p1;
            $p2 = (int)$p2;
            for($p=$p1; $p <= $p2; $p++){
                $res[] = $p;
            }
        }
        return $res;
    }
    
} // end class
