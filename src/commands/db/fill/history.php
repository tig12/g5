<?php
/******************************************************************************
    
    Fills database from scratch with historical data.
    WARNING : all existing tables are dropped and recreated
    Precise order of the executed steps must be respcted to obtain a coherent result.
    
    @license    GPL
    @history    2020-08-17 20:18:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\commands\db\admin\dbcreate;

use g5\commands\cura\CuraRouter;
use g5\commands\cura\A\raw2tmp as raw2tmpA;
use g5\commands\cura\D6\raw2tmp as raw2tmpD6;
use g5\commands\cura\D10\raw2tmp as raw2tmpD10;
use g5\commands\cura\E1_E3\raw2tmp as raw2tmpE1E3;
use g5\commands\cura\all\tweak2tmp as tweak2tmpCura;
use g5\commands\newalch\ertel4391\raw2tmp as raw2tmpErtel4391;
use g5\commands\newalch\ertel4391\tweak2tmp as tweak2tmpErtel4391;
use g5\commands\csicop\si42\raw2tmp as raw2tmpSi42;
use g5\commands\csicop\si42\addCanvas1;
use g5\commands\csicop\irving\raw2tmp as raw2tmpIrving;


class history implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return Empty string, echoes the reports of individual commands progressively. 
    **/                                                                  
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "ERROR, useless parameter : {$params[0]}\n";
        }
        
        //
        //  Create tables in database
        //
//        echo dbcreate::execute([]);
        
        //
        //  Create tmp files from raw data
        //
        $datafiles = CuraRouter::computeDatafiles('A');
        foreach($datafiles as $datafile){
//            echo raw2tmpA::execute([$datafile, 'raw2tmp', 'small']);
        }
//        echo raw2tmpD6::execute(['D6', 'raw2tmp']);
//        echo raw2tmpD10::execute(['D10', 'raw2tmp']);
//        echo raw2tmpE1E3::execute(['E1', 'raw2tmp', 'small']);
//        echo raw2tmpE1E3::execute(['E3', 'raw2tmp', 'small']);
        $datafiles = CuraRouter::computeDatafiles('all');
        foreach($datafiles as $datafile){
//            echo tweak2tmpCura::execute([$datafile, 'tweak2tmp']);
        }
//        echo raw2tmpErtel4391::execute([]);
//        echo tweak2tmpErtel4391::execute([]);
//        echo raw2tmpSi42::execute([]);
//        echo addCanvas1::execute([]);    
        echo raw2tmpIrving::execute([]);
        return '';
    }
    
    
}// end class
