<?php
/********************************************************************************
    Builds a list of XXX mathematicians ranked by eminence.
    Data source : book
        Une histoire des mathématiques
        Routes et dédales
        Amy Dahan-Dalmedico
        Jeanne Peiffer
        Editions du Seuil, 1986
    Input
        data/auxiliary/maths/peiffer-dahan-dalmenico/peiffer-dahan-dalmenico.txt
    Output
        data/build/eminence/math/pdd.yml
    
    @license    GPL
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
    @history    2020-08-17 23:36:30+02:00, Thierry Graff : Conert from raw2full to raw2tmp
********************************************************************************/
namespace g5\commands\eminence\math;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\{Source, SourceI, Group};
use tiglib\arrays\sortByKey;

// *****************************************
//          Model class
// *****************************************
class PDDal implements SourceI {
// PeifferDahanDalmedico

    // TRUST_LEVEL not defined, using value of class Newalch
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory data/model/source
    **/
    const SOURCE_DEFINITION = 'eminence' . DS . 'math' . DS . 'peiffer-dahan-dalmenico.yml';

    /** Slug of the group in db **/
    const GROUP_SLUG = 'peiffer-dahan-dalmenico';
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for 5muller_writers.xlsx. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['model'] . DS . self::SOURCE_DEFINITION);
    }
    
    // *********************** Raw files manipulation ***********************
    
    /**
        @return Path to the raw file 5muller_writers.csv coming from newalch
    **/
    public static function rawFilename(){
        return implode(DS, [Config::$data['dirs']['raw'], 'eminence', 'math', 'peiffer-dahan-dalmenico.txt']);
    }
    
    /** Loads 5muller_writers.csv in a regular array **/
    public static function loadRawFile(){
        return file(self::rawFilename(), FILE_IGNORE_NEW_LINES);
    }                                                                                              
    
    // *********************** Tmp file manipulation ***********************
    
    /**
        @return Path to the csv file stored in data/tmp/newalch/
    **/
    public static function tmpFilename(){
        return implode(DS, [Config::$data['dirs']['tmp'], 'eminence', 'math', 'peiffer-dahan-dalmenico.csv']);
    }
    
}


// *****************************************
//          Implementation of Command
// *****************************************
class pdd implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch muller1083 look gnr
    **/
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
        
    **/
    private static function exec_raw2tmp(){
        $report =  "--- pdd raw2tmp ---\n";
        $lines = PDDal::loadRawFile();
        $SEP = ';'; // separator in resulting csv
        $N = 0;
        $p = '/(.*?), (\d.*)\./';
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
            $res[] = [
                'name' => $m[1],
                'score' => count($pages),
                'pages' => self::computePages($m[2]),
            ];
        }
        $res = sortByKey::compute($res, 'score');
        //
        $res2 = "NAME{$SEP}SCORE{$SEP}PAGES\n";
        for($i=count($res)-1; $i >= 0; $i--){
            $cur = $res[$i];
            $cur['pages'] = implode('+', $cur['pages']);
            $res2 .= implode($SEP, $cur) . "\n";
        }
        //
        $outfile = PDDal::tmpFilename();
        file_put_contents($outfile, $res2);
        $report .= "Wrote $N records in $outfile\n";
        return $report;
    }
    
    /**
        Auxiliary of exec_raw2tmp()
        @param  $str    Examples :
                        296
                        103, 107, 177
                        125, 174-176
        @return Array of page numbers
    **/
    public static function computePages($str){
        $parts = explode(', ', $str);
        $res = [];
        foreach($parts as $part){
            $part = trim($part);
            if(is_numeric($part)){
                // single page number
                $res[] = $part;
            }
            else {
                // page range, like 174-176
                $tmp = explode('-', $part);
                if(count($tmp) != 2){
                    echo "ERROR in computePages($str) : $part\n";
                    continue;
                }
                [$p1, $p2] = $tmp;
                for($p=$p1; $p <= $p2; $p++){
                    $res[] = $p;
                }
            }
        }
        return $res;
    }
    
}// end class
