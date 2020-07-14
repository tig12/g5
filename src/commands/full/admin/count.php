<?php
/******************************************************************************
    
    Various numers on g5 db.
    
    @license    GPL
    @history    2020-07-14 02:54:44+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\full\admin;

use g5\G5;
use g5\model\DB5;
//use g5\model\Source;
// use g5\model\Person;
// use g5\model\Group;
// use g5\model\Source;
// use g5\Config;
use tiglib\filesystem\globRecursive;
use g5\patterns\Command;


class count implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
                
        @return String report by default
                if $params['return-type'] = 'array',
                returns key value pairs containing metrics
    **/
    public static function execute($params=[]){
        $res = [
            'person' => self::count_persons(),
        ];
        return print_r($res);
    }
    
    
    // ******************************************************
    public static function count_persons(){
        $files = globRecursive::execute(DB5::$DIR_PERSON . DS . '*.yml');
        return count($files);
    }
    
    
}// end class
