<?php
/******************************************************************************
    Rebuilds
    
    data/7-full/index/source/id-uid.txt
    
    @license    GPL
    @history    2020-06-03 01:24:09+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\full\index;

use g5\G5;
use g5\model\DB5;
//use g5\model\Source;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\sortByKey;


class sourceiduid implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Parses one html cura file of serie A (locally stored in directory data/raw/cura.free.fr)
        Stores each person of the file in a distinct yaml files, in 7-full/persons/
        
        Merges the original list (without names) with names contained in file 902gdN.html
        Merge is done using birthdate.
        Merge is not complete because of doublons (persons born the same day).
        
        @param  $params empty Array
        @return String report
    **/
    public static function execute($params=[]): string{
        return Source::reindexIdUid();
    }
    
}// end class
