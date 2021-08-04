<?php
/******************************************************************************
    
    Fills database from scratch with historical data.
    WARNING : all existing tables are dropped and recreated.
    Precise order of the executed steps must be respcted to obtain a coherent result.
    
    @license    GPL
    @history    2020-08-17 20:18:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\commands\cura\CuraRouter;

// for information sources
use g5\commands\gauquelin\LERRCP;
use g5\commands\muller\AFD;
use g5\commands\newalch\Newalch;
use g5\commands\cura\Cura;
use g5\G5;

use g5\commands\db\create\dbcreate;
use g5\commands\db\fill\source;
use g5\commands\db\fill\occu;
use g5\commands\db\fill\occustats;
use g5\commands\db\fill\stats;
use g5\commands\db\fill\search;

// raw2tmp
use g5\commands\cura\A\raw2tmp                  as raw2tmpA;
use g5\commands\cura\A\addGeo                   as addGeoA;
use g5\commands\cura\D6\raw2tmp                 as raw2tmpD6;
use g5\commands\cura\D6\addGeo                  as addGeoD6;
use g5\commands\cura\D10\raw2tmp                as raw2tmpD10;
use g5\commands\cura\E1_E3\raw2tmp              as raw2tmpE1E3;
use g5\commands\cura\all\tweak2tmp              as tweak2tmpCura;

use g5\commands\newalch\ertel4391\raw2tmp       as raw2tmpErtel4391;
use g5\commands\newalch\ertel4391\tweak2tmp     as tweak2tmpErtel4391;

use g5\commands\newalch\muller1083\raw2tmp      as raw2tmpMuller1083;
use g5\commands\newalch\muller1083\tweak2tmp    as tweak2tmpMuller1083;
use g5\commands\newalch\muller1083\fixGnr       as fixGnrMuller1083;

use g5\commands\newalch\muller402\raw2tmp       as raw2tmpMuller402;
use g5\commands\newalch\muller402\tweak2tmp     as tweak2tmpMuller402;
use g5\commands\newalch\muller402\addA6         as addA6Muller402;
use g5\commands\newalch\muller402\raw2tmp100    as raw2tmpMuller100;

use g5\commands\csicop\si42\raw2tmp             as raw2tmpSi42;
use g5\commands\csicop\si42\addCanvas1          as addCanvas1Si42;
use g5\commands\csicop\irving\raw2tmp           as raw2tmpIrving;
use g5\commands\csicop\irving\addD10            as addD10Irving;

use g5\commands\muller\afd3women\raw2tmp        as raw2tmpAfd3Women;

// tmp2db
use g5\commands\cura\A\tmp2db                   as tmp2dbA;
use g5\commands\cura\D6\tmp2db                  as tmp2dbD6;
use g5\commands\cura\D10\tmp2db                 as tmp2dbD10;
use g5\commands\cura\E1_E3\tmp2db               as tmp2dbE1E3;
use g5\commands\newalch\muller1083\tmp2db       as tmp2dbMuller1083;
use g5\commands\newalch\muller402\tmp2db        as tmp2dbMuller402;
use g5\commands\newalch\muller402\tmp2db100     as tmp2db100Muller402;
use g5\commands\csicop\irving\tmp2db            as tmp2dbIrving;

class history implements Command {
    
    /** 
        Possible values of the command
    **/
    const POSSIBLE_PARAMS = [
        'tmp'       => 'Build files in data/tmp',
        'db'        => 'Fill database with tmp files',
        'finalize'  => 'Finalize DB (stats, groups, search)',
        'all'       => 'Executes all steps',
    ];
    
    // *****************************************
    // Implementation of Command
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
        
        $filesCura = CuraRouter::computeDatafiles('all');
        $filesCuraA = CuraRouter::computeDatafiles('A');
        
        //
        //  1 - Create tmp files from raw data
        //
        if($param == 'tmp' || $param == 'all'){
            echo "***********************\n";
            echo "*** Build tmp files ***\n";
            echo "***********************\n";
            foreach($filesCuraA as $datafile){
                echo raw2tmpA::execute([$datafile, 'raw2tmp', 'small']);
                echo addGeoA::execute([$datafile, 'addGeo', 'small']);
            }
            echo raw2tmpD6::execute(['D6', 'raw2tmp']);
            echo addGeoD6::execute(['D6', 'addGeo']); // tmp code - addGeo needs to be fixed
            echo raw2tmpD10::execute(['D10', 'raw2tmp']);
            echo raw2tmpE1E3::execute(['E1', 'raw2tmp', 'small']);
            echo raw2tmpE1E3::execute(['E3', 'raw2tmp', 'small']);
            foreach($filesCura as $datafile){
                echo tweak2tmpCura::execute([$datafile, 'tweak2tmp']);
            }
            
            echo raw2tmpErtel4391::execute([]);
            echo tweak2tmpErtel4391::execute([]);
            
            echo raw2tmpMuller1083::execute([]);
            echo tweak2tmpMuller1083::execute([]);
            echo fixGnrMuller1083::execute(['update']);
            
            echo raw2tmpSi42::execute([]);                                   
            echo addCanvas1Si42::execute([]);
            echo raw2tmpIrving::execute([]);
            echo addD10Irving::execute([]);
            
            echo raw2tmpMuller402::execute([]);
            echo tweak2tmpMuller402::execute([]);
            echo addA6Muller402::execute(['update']);
            echo raw2tmpMuller100::execute([]);
            
            echo raw2tmpAfd3Women::execute([]);
        }
        
        //
        //  2 - Import tmp files to db
        //
        if($param == 'db' || $param == 'all'){
            echo "***********************\n";
            echo "***  Fill database  ***\n";
            echo "***********************\n";
            
            echo dbcreate::execute([]);
            echo source::execute([LERRCP::SOURCE_DEFINITION_FILE]);
            echo source::execute([AFD::SOURCE_DEFINITION_FILE]);
            echo source::execute([Cura::SOURCE_DEFINITION_FILE]);
            echo source::execute([Newalch::SOURCE_DEFINITION_FILE]);
            echo source::execute([G5::SOURCE_DEFINITION_FILE]);
            echo occu::execute();
            
            foreach($filesCuraA as $datafile){
                echo tmp2dbA::execute([$datafile, 'tmp2db', 'small']);
            }
            echo tmp2dbD6::execute(['D6', 'tmp2db', 'small']);
            echo tmp2dbD10::execute(['D10', 'tmp2db', 'small']);
            echo tmp2dbE1E3::execute(['E1', 'tmp2db', 'small']);
            echo tmp2dbE1E3::execute(['E3', 'tmp2db', 'small']);
            echo tmp2dbMuller1083::execute(['small']);
            echo tmp2dbMuller402::execute(['small']);
            echo tmp2db100Muller402::execute(['small']);
            echo tmp2dbIrving::execute(['small']);
        }
        
        if($param == 'finalize' || $param == 'all'){
            echo "***************************\n";
            echo "***  Finalize database  ***\n";
            echo "***************************\n";
            
            echo stats::execute(['small']);
            echo occustats::execute();
            echo occugroup::execute();
            echo search::execute();
        }
        
        return '';
    }
    
} // end class
