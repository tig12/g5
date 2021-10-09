<?php
/********************************************************************************
    Parses file 902gdN.html
    
    @license    GPL
    @history    2017-04-27 11:16:42+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq;

use g5\commands\gauq\Cura;

class CuraNames{
    
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
        $filename =self::rawFilename();
        $raw = Cura::loadRawFile('N');
        preg_match_all('#<pre>\s*(DAY.*?NAME)\s*(.*?)\s*</pre>#sm', $raw, $m);
        // check that the lines are present
        if(!isset($m[2]) || count($m[2]) != 2){
            throw new \Exception("Unable to parse " . $filename);
        }
        $fieldnames = explode(Cura::HTML_SEP, $m[1][0]);
        if(count($fieldnames) != 6){
            throw new \Exception("Unable to parse " . $filename . " (there should be 6 fields per line)");
        }
        $res = [];
        for($i=0; $i < 2; $i++){
            $lines = explode("\n", $m[2][$i]);
            foreach($lines as $line){
                $values = explode(Cura::HTML_SEP, $line);
                $fields = [];
                for($j=0; $j < 6; $j++){
                    $fields[$fieldnames[$j]] = $values[$j];
                }
                if(!isset($res[$fields['FILE']])){
                    $res[$fields['FILE']] = [];
                }
                $res[$fields['FILE']][] = [
                    'day'   => Cura::computeDay($fields),
                    'pro'   => $fields['PRO'], // profession kept because sometimes useful (for ex in A4 and A5)
                    'name'  => trim($fields['NAME']),
                ];
            }
        }
        return $res;
    }
    
    
}// end class

