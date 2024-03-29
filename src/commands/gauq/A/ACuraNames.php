<?php
/********************************************************************************
    Parses file 902gdN.html with names of A files
    
    TODO This class and file 902gdN.html could be removed,
         because names are present in files 902gdA*y.html
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 11:16:42+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura5;

class ACuraNames{
    
    // ******************************************************
    public static function rawFilename(){
        return '902gdN.html';
    }
    
    // ******************************************************
    /**
        Parses file 902gdN.html
        @return Associative array ; the keys are serie names (ex 'A1')
        @throws Exception if unable to parse
    **/
    public static function parse(){
        $filename = self::rawFilename();
        $raw = LERRCP::loadRawFile('N');
        preg_match_all('#<pre>\s*(DAY.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        // check that the lines are present
        if(!isset($m[2]) || count($m[2]) != 2){
            throw new \Exception("Unable to parse " . $filename);
        }
        $fieldnames = explode(Cura5::HTML_SEP, $m[1][0]);
        if(count($fieldnames) != 6){
            throw new \Exception("Unable to parse " . $filename . " (there should be 6 fields per line)");
        }
        $res = [];
        for($i=0; $i < 2; $i++){
            $lines = explode("\n", $m[2][$i]);
            foreach($lines as $line){
                $values = explode(Cura5::HTML_SEP, $line);
                $fields = [];
                for($j=0; $j < 6; $j++){
                    $fields[$fieldnames[$j]] = $values[$j];
                }
                if(!isset($res[$fields['FILE']])){
                    $res[$fields['FILE']] = [];
                }
                $res[$fields['FILE']][] = [
                    'day'   => Cura5::computeDay($fields),
                    'pro'   => $fields['PRO'], // profession kept because sometimes useful (for ex in A4 and A5)
                    'name'  => trim($fields['NAME']),
                ];
            }
        }
        return $res;
    }
    
    
}// end class

