<?php
/******************************************************************************
    
    Fills database from scratch with historical data.
    WARNING : all existing tables are dropped and recreated.
    Precise order of the executed steps must be respcted to obtain a coherent result.
    
    @license    GPL
    @history    2020-08-17 20:18:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\commands\gauq\GauqRouter;

// for information sources
use g5\G5;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;
use g5\commands\Newalch;
use g5\commands\gauq\Cura;
use g5\commands\wd\Wikidata;

use g5\commands\db\init\dbcreate;
use g5\commands\db\init\occus1;
use g5\commands\db\init\occus2;
use g5\commands\db\fill\source                  as fillsource;
use g5\commands\db\fill\person                  as fillperson;

// raw2tmp
use g5\commands\gauq\A\raw2tmp                  as raw2tmpA;
use g5\commands\gauq\A\addGeo                   as addGeoA;
use g5\commands\gauq\D6\raw2tmp                 as raw2tmpD6;
use g5\commands\gauq\D6\addGeo                  as addGeoD6;
use g5\commands\gauq\D10\raw2tmp                as raw2tmpD10;
use g5\commands\gauq\E1_E3\raw2tmp              as raw2tmpE1E3;
use g5\commands\gauq\all\tweak2tmp              as tweak2tmpGauq;

use g5\commands\ertel\ertel4391\raw2tmp         as raw2tmpErtelSport;
use g5\commands\ertel\ertel4391\tweak2tmp       as tweak2tmpErtelSport;
use g5\commands\ertel\ertel4391\fixA1           as fixA1ErtelSport;

use g5\commands\muller\m5medics\raw2tmp         as raw2tmpM5Medics;
use g5\commands\muller\m5medics\tweak2tmp       as tweak2tmpM5Medics;
use g5\commands\muller\m5medics\fixGnr          as fixGnrM5Medics;

use g5\commands\muller\m1writers\raw2tmp        as raw2tmpM1Writers;
use g5\commands\muller\m1writers\tweak2tmp      as tweak2tmpM1Writers;
use g5\commands\muller\m1writers\gauq           as gauqM1Writers;
use g5\commands\muller\m1writers\raw2tmp100     as raw2tmpM1Writers100;

use g5\commands\csicop\si42\raw2tmp             as raw2tmpSi42;
use g5\commands\csicop\si42\addCanvas1          as addCanvas1Si42;
use g5\commands\csicop\irving\raw2tmp           as raw2tmpIrving;
use g5\commands\csicop\irving\addD10            as addD10Irving;
use g5\commands\muller\m3women\raw2tmp          as raw2tmpM3Women;
use g5\commands\muller\m2men\raw2tmp            as raw2tmpM2Men;

// tmp2db
use g5\commands\gauq\A\tmp2db                   as tmp2dbA;
use g5\commands\gauq\A\A6occu                   as A6occu;
use g5\commands\gauq\D6\tmp2db                  as tmp2dbD6;
use g5\commands\gauq\D10\tmp2db                 as tmp2dbD10;
use g5\commands\gauq\E1_E3\tmp2db               as tmp2dbE1E3;
use g5\commands\muller\m1writers\tmp2db         as tmp2dbM1writers;
use g5\commands\muller\m1writers\tmp2db100      as tmp2dbM1writers100;
use g5\commands\muller\m2men\tmp2db             as tmp2dbM2men;
use g5\commands\muller\m3women\tmp2db           as tmp2dbM3women;
use g5\commands\muller\m5medics\tmp2db          as tmp2dbM5medics;
use g5\commands\csicop\irving\tmp2db            as tmp2dbIrving;

// finalize
use g5\commands\db\init\stats;
use g5\commands\db\init\search;

// export
use g5\commands\gauq\all\export                 as exportCura;
use g5\commands\muller\m5medics\export          as exportM5Medics;
use g5\commands\muller\m1writers\export         as exportM1Writers;
use g5\commands\muller\m1writers\export100      as exportM1Writers100;
use g5\commands\csicop\irving\export            as exportIrving;
use g5\commands\db\export\alloccus              as exportAllOccus;

class all implements Command {
    
    /** 
        Possible values of the command
    **/
    const POSSIBLE_PARAMS = [
        'tmp'       => 'Build files in data/tmp',
        'db'        => 'Fill database with tmp files',
        'finalize'  => 'Finalize DB (stats, groups, search)',
        'export'    => 'Exports the groups in zipped csv files',
        'all'       => 'Executes all steps',
    ];
    
    /** 
        @param  $params empty array
        @return Empty string, echoes the reports of individual commands progressively. 
    **/                                                                  
    public static function execute($params=[]): string {
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "    $k : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]} - this command takes only one parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $filesGauq = GauqRouter::computeDatafiles('all');
        $filesGauqA = GauqRouter::computeDatafiles('A');
        
        $t1 = microtime(true);
        
        //
        //  1 - Create tmp files from raw data
        //
        if($param == 'tmp' || $param == 'all'){
            echo "***********************\n";
            echo "*** Build tmp files ***\n";
            echo "***********************\n";
            foreach($filesGauqA as $datafile){
                echo raw2tmpA::execute([$datafile, 'raw2tmp', 'small']);
                echo addGeoA::execute([$datafile, 'addGeo', 'small']);
            }
            echo raw2tmpD6::execute(['D6', 'raw2tmp']);
            echo addGeoD6::execute(['D6', 'addGeo']); // tmp code - addGeo needs to be fixed
            echo raw2tmpD10::execute(['D10', 'raw2tmp']);
            echo raw2tmpE1E3::execute(['E1', 'raw2tmp', 'small']);
            echo raw2tmpE1E3::execute(['E3', 'raw2tmp', 'small']);
            foreach($filesGauq as $datafile){
                echo tweak2tmpGauq::execute([$datafile, 'tweak2tmp']);
            }
            
            echo raw2tmpErtelSport::execute([]);
            echo tweak2tmpErtelSport::execute([]);
            echo fixA1ErtelSport::execute(['update']);
            
            echo raw2tmpM5Medics::execute([]);
            echo tweak2tmpM5Medics::execute([]);
            echo fixGnrM5Medics::execute(['update']);
            
            echo raw2tmpSi42::execute([]);                                   
            echo addCanvas1Si42::execute([]);
            echo raw2tmpIrving::execute([]);
            echo addD10Irving::execute([]);
            
            echo raw2tmpM1Writers::execute([]);
            echo tweak2tmpM1Writers::execute([]);
            echo gauqM1Writers::execute(['update']);
            echo raw2tmpM1Writers100::execute([]);
            
            echo raw2tmpM3Women::execute([]);
            
            echo raw2tmpM2Men::execute([]);
        }
        
        //
        //  2 - Import tmp files to db
        //
        if($param == 'db' || $param == 'all'){

            echo "***********************\n";
            echo "***  Fill database  ***\n";
            echo "***********************\n";
            echo dbcreate::execute([]);
            echo fillsource::execute([LERRCP::SOURCE_DEFINITION_FILE]);
            echo fillsource::execute([Muller::SOURCE_DEFINITION_FILE]);
            echo fillsource::execute([G5::SOURCE_DEFINITION_FILE]);
            echo fillsource::execute([Cura::SOURCE_DEFINITION_FILE]);
            echo fillsource::execute([Newalch::SOURCE_DEFINITION_FILE]);
            echo fillsource::execute([Wikidata::SOURCE_DEFINITION_FILE]);
            echo occus1::execute();
            
            foreach($filesGauqA as $datafile){
                echo tmp2dbA::execute([$datafile, 'tmp2db', 'small']);
            }
            echo fillperson::execute(['A1.yml']);
            
            echo A6occu::execute([]);
            echo tmp2dbD6::execute(['D6', 'tmp2db', 'small']);
            echo tmp2dbD10::execute(['D10', 'tmp2db', 'small']);
            echo tmp2dbE1E3::execute(['E1', 'tmp2db', 'small']);
            echo tmp2dbE1E3::execute(['E3', 'tmp2db', 'small']);
            
            echo tmp2dbM5medics::execute(['small']);
            
            echo tmp2dbM1writers::execute(['small']);
            
            echo tmp2dbM1writers100::execute(['small']);
            
            echo tmp2dbIrving::execute(['small']);
            
            echo tmp2dbM3women::execute(['small']);
            echo fillperson::execute(['muller-234-women.yml']);
            
            echo tmp2dbM2men::execute(['small']);
            echo fillperson::execute(['muller-612-men.yml']);
            
            echo occus2::execute();
        }
        
        if($param == 'finalize' || $param == 'all'){
            echo "***************************\n";
            echo "***  Finalize database  ***\n";
            echo "***************************\n";
            echo stats::execute(['small']);
//            echo search::execute();
        }
        
        if($param == 'export' || $param == 'all'){
            echo "***************************\n";
            echo "***    Export groups    ***\n";
            echo "***************************\n";
            foreach($filesGauqA as $datafile){
                echo exportCura::execute([$datafile, 'export']);
            }
            echo exportCura::execute(['D6', 'export']);
            echo exportCura::execute(['D10', 'export']);
            echo exportCura::execute(['E1', 'export']);
            echo exportCura::execute(['E3', 'export']);
            echo exportM5Medics::execute([]);
            echo exportM1Writers::execute([]);
            echo exportM1Writers100::execute([]);
            echo exportIrving::execute([]);
            //
            echo exportAllOccus::execute([]);
        }
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        
        echo "====== Execution of all commands in $dt s ======\n";
        return '';
    }
    
} // end class
