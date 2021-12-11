<?php
/********************************************************************************

person
personLabel
familynameLabel
genderLabel
occupation
occupationLabel
linkcount
isni
macTutor
birthdate
birthplace
birthplaceLabel
birthiso3166
birthgeonamesid
birthcoords
deathdate
deathplace
deathplaceLabel
deathiso3166
deathgeonamesid
deathcoords
deathcause
deathcauseLabel
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-16 12:16:35+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\eminence\math;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\DB5;
use g5\commands\wd\Wikidata;
use tiglib\arrays\csvAssociative;
use tiglib\arrays\sortByKey;

class wd implements Command {
    
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
        
        // TODO Change when wikidata is integrated
        $filepath = '/home/thierry/dev/astrostats/gauquelin5/data/raw/z.1-raw/z.wikidata.org-BCK/z-person-lists/science/maths.csv';
        $rows = csvAssociative::compute($filepath, Wikidata::RAW_CSV_SEP);
        $persons = []; // keys = wdid
        $N = 0;
        foreach($rows as $row){
            $wdid = str_replace(Wikidata::ENTITY_URL . '/', '', $row['person']);
            if(isset($persons[$wdid])){
                continue;
            }
            $persons[$wdid] = $row;
            $persons[$wdid]['WDID'] = $wdid;
            $N++;
        }
        $res = sortByKey::compute($persons, 'linkcount');
        for($i=count($res)-1; $i >= 0; $i--){
            $cur = $res[$i];
            echo $cur['linkcount'] . "\t" . $cur['familynameLabel'] . "\n";
if($i == count($res)-20) break;
        }
echo "$N\n";
        return '';
    }
    
}// end class    
