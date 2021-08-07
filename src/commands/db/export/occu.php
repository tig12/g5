<?php
/******************************************************************************
    
    Stores all persons with gien occupation codes in a csv file
    By default, the generated file is compressed (using zip).
    
    @license    GPL
    @history    2020-08-29 19:42:54+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\patterns\Command;
use g5\Config;
use g5\model\Person;
use g5\model\Group;
use g5\model\Occupation;
use g5\commands\gauquelin\LERRCP;
use g5\commands\muller\AFD;

class occu implements Command {
    
    /** 
        Supplementary parameters that are not hadled by CLI interface
    **/
    const PARAMS = [
        // if true the generated file name will be prefixed by the number of records
        'prefix_with_number' => true,
    ];
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params array containing 2 or 3 elements :
                        - a list of profession codes separated by +
                        - The path to the output file, relative to directory specified in config.yml by dirs / output
                        - An optional string "nozip"
    **/
    public static function execute($params=[]): string {
        $msg = "USAGE : php run-g5.php db export occu <profession codes> <path to output file> [nozip]\n"
            . "  To export several professions, separate the codes by \"+\"\n"
            . "  Use 'nozip' as 3rd parameter to export a csv instead of a zipped csv :\n"
            . "Examples :\n"
            . "  php run-g5.php db export skier path/to/output.csv\n"
            . "  php run-g5.php db export skier path/to/output.csv nozip\n"
            . "  php run-g5.php db export skier+writer path/to/output.csv\n"
            ;
        if(count($params) > 3){
            return "USELESS PARAMETER : '{$params[3]}'\n" . $msg;
        }
        if(count($params) == 3 && $params[2] != 'nozip'){
            return "INVALID PARAMETER : '{$params[2]}'\n" . $msg;
        }
        if(count($params) < 2){
            return "MISSING PARAMETER'\n" . $msg;
        }
        $dozip = true;
        if(count($params) == 3){
            $dozip = false;
        }
        
        $occus1 = explode('+', $params[0]);
        $occus = [];
        foreach($occus1 as $occu){
            $children = Occupation::getDescendants($occu, includeSeed:true);
            if(!empty($children)){
                $occus = array_merge($occus, $children);
            }
        }
        
        $outfile = Config::$data['dirs']['output'] . DS . $params[1];
        
        $persons = Person::getByOccu($occus);
        $g = new Group();
        $g->data['person-members'] =& $persons;
        $g->personMembersComputed = true;
        
        $csvFields = [
            'GQID',
            'MUID',
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'TZO',
            'DATE-UT',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.date-ut' => 'DATE-UT',
            'birth.tzo' => 'TZO',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return ($p->data['ids-in-sources'][LERRCP::SOURCE_SLUG] ?? '');
            },
            'MUID' => function($p){
                return AFD::ids_in_sources2mullerId($p->data['ids-in-sources']);
            },
        ];
        
        // sorts by name
        $sort = function($a, $b){
            $nameA = $a->data['name']['family'] . ' ' . $a->data['name']['given'];
            $nameB = $b->data['name']['family'] . ' ' . $b->data['name']['given'];
            return $nameA <=> $nameB;
        };
        
        $filters = [];
        
        if(self::PARAMS['prefix_with_number']){
            $outfile = self::prefix_with_number($outfile, count($persons));
        }
//echo "$outfile\n"; exit;
        
        [$report, $file] =  $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            sort:       $sort,
            filters:    $filters,
            dozip:      $dozip
        );
        
        return $report;
    }
    
    
    // ************************* Auxiliary functions *****************************
    private static function prefix_with_number($filename, $N){
        $dir = dirname($filename);
        $base = basename($filename);
        return $dir . DS . "$N-$base";
    }
    
    
}// end class
