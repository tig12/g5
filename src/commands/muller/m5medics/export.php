<?php
/********************************************************************************
    Generates data/output/history/1994-muller5-medics/muller-1083-medics.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-09-12 17:27:59+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m5medics;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Names_fr;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;

class export implements Command {
                                                                                              
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1994-muller5-medics';
    
    const OUTPUT_FILE = 'muller-1083-medics.csv';
    
    /**  Trick to access to $sourceSlug inside $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php muller m5medics export [nozip]
        If called without parameter, the output is compressed (using zip)
        @param $params array containing 0 or 1 element :
                       - An optional string "nozip"
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 1){
            return "WRONG USAGE : useless parameter : '{$params[1]}'\n";
        }
        if(count($params) == 1 && $params[0] != 'nozip'){
            return "WRONG USAGE : invalid parameter : '{$params[0]}' - possible value : 'nozip'\n";
        }
        $dozip = true;
        if(count($params) == 1){
            $dozip = false;
        }
        
        $report = '';
        
        $g = Group::createFromSlug(M5medics::GROUP_SLUG); // DB
        
        self::$sourceSlug = M5medics::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'DATE-UT',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
            // specific to this group, coming from original raw file
            'ELECTDAT',
            'ELECTAGE',
            'STBDATUM',
            'SONNE',
            'MOND',
            'VENUS',
            'MARS',
            'JUPITER',
            'SATURN',
            'SO_',
            'MO_',
            'VE_',
            'MA_',
            'JU_',
            'SA_',
            'PHAS_',
            'AUFAB',
            'NIENMO',
            'NIENVE',
            'NIENMA',
            'NIENJU',
            'NIENSA',
        ];
        
        $map = [
            'partial-ids.' . Muller::SOURCE_SLUG => 'MUID',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
            'birth.date-ut' => 'DATE-UT',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
            // stuff coming from original raw file
// BUG HERE - 
// some records: raw.history.0
// some records: raw.history.1
/* 
            'raw.history.0.raw.ELECTDAT' => 'ELECTDAT',
            'raw.history.0.raw.ELECTAGE' => 'ELECTAGE',
            'raw.history.0.raw.STBDATUM' => 'STBDATUM',
            'raw.history.0.raw.SONNE' => 'SONNE',
            'raw.history.0.raw.MOND' => 'MOND',
            'raw.history.0.raw.VENUS' => 'VENUS',
            'raw.history.0.raw.MARS' => 'MARS',
            'raw.history.0.raw.JUPITER' => 'JUPITER',
            'raw.history.0.raw.SATURN' => 'SATURN',
            'raw.history.0.raw.SO_' => 'SO_',
            'raw.history.0.raw.MO_' => 'MO_',
            'raw.history.0.raw.VE_' => 'VE_',
            'raw.history.0.raw.MA_' => 'MA_',
            'raw.history.0.raw.JU_' => 'JU_',
            'raw.history.0.raw.SA_' => 'SA_',
            'raw.history.0.raw.PHAS_' => 'PHAS_',
            'raw.history.0.raw.AUFAB' => 'AUFAB',
            'raw.history.0.raw.NIENMO' => 'NIENMO',
            'raw.history.0.raw.NIENVE' => 'NIENVE',
            'raw.history.0.raw.NIENMA' => 'NIENMA',
            'raw.history.0.raw.NIENJU' => 'NIENJU',
            'raw.history.0.raw.NIENSA' => 'NIENSA',
*/
        ];
        
        $fmap = [
            'FNAME' => function($p){
                // ok because all members are french
                return Names_fr::computeFamilyName($p->data['name']['family'], $p->data['name']['nobl']);
            },
            'GQID' => function($p){
                return $p->data['ids-in-sources'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by Müller id
        $sort = function($a, $b){
             return $a->data['ids-in-sources'][self::$sourceSlug] <=> $b->data['ids-in-sources'][self::$sourceSlug];
        };
        
        $filters = [];
        
        [$exportReport, $exportFile, $N] = 
        $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            sort:       $sort,
            filters:    $filters,
            dozip:      $dozip,
        );
        $g->data['download'] = str_replace(Config::$data['dirs']['output'] . DS, '', $exportFile);
        $g->update(updateMembers:false);
        $report .= $exportReport;
        return $report;
    }
    
} // end class    
