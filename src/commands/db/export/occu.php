<?php
/******************************************************************************
    
    Stores all persons with gien occupation codes in a csv file
    
    @license    GPL
    @history    2020-08-29 19:42:54+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\patterns\Command;
use g5\Config;
use g5\commands\newalch\Newalch;
use g5\model\{Person,Group,Occupation};

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
        @param  $params array containing 2 elements :
                        - a list of profession codes separated by +
                        - The path to the output file
    **/
    public static function execute($params=[]): string {
        $msg = "USAGE : php run-g5.php db export occu <profession codes> <path to output file>\n"
            . "To export several professions, separate the codes by \"+\"\n"
            . "Examples :\n"
            . "php run-g5.php db export SPO /path/to/output.csv\n"
            . "php run-g5.php db export SPO+WR /path/to/output.csv\n"
            ;
        if(count($params) != 2){
            return $msg;
        }
        
        $occus1 = explode('+', $params[0]);
        $occus = [];
        foreach($occus1 as $occu){
            $children = Occupation::getChildren($occu);
            if(!empty($children)){
                $occus = array_merge($occus, $children);
            }
            $occus[] = $occu;
        }
        
        $outfile = $params[1];
        
        $persons = Person::getByOccu($occus);
        $g = new Group();
        $g->data['members'] =& $persons;
        $g->membersComputed = true;
        
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
            'GID' => function($p){
                return $p->data['ids_in_sources']['cura'] ?? '';
            },
            'MUID' => function($p){
                // TODO refactor, this should be called in raw2tmp or tmp2db of newalch files.
                return Newalch::ids_in_sources2muId($p->data['ids_in_sources']);
            },
        ];
        
        // sorts by name
        $sort = function($a, $b){
            $nameA = $a->data['name']['family'] . ' ' . $a->data['name']['given'];
            $nameB = $b->data['name']['family'] . ' ' . $b->data['name']['given'];
            //return $a->data['name']['family'] <=> $b->data['name']['family'];
            return $nameA <=> $nameB;
        };
        
        if(self::PARAMS['prefix_with_number']){
            $outfile = self::prefix_with_number($outfile, count($persons));
        }
        
        return $g->exportCsv($outfile, $csvFields, $map, $fmap, $sort);
    }
    
    
    // ************************* Auxiliary functions *****************************
    private static function prefix_with_number($filename, $N){
        $dir = dirname($filename);
        $base = basename($filename);
        return $dir . DS . "$N-$base";
    }
    
    
}// end class
