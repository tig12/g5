<?php
/********************************************************************************
    Shortcut to add command, which handles BC add and update.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-04-20 08:58:12+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use g5\G5;
use tiglib\patterns\Command;

class update implements Command {
    
    /** 
        @param  $params See doc in add.php
        @return String report
    **/
    public static function execute($params=[]): string{
        if(count($params) == 1){
            // called only with act slug
            $params[] = 'action=upd';
        }
        else if(count($params) == 2){
            // called with act slug + optional parameters
            $optionals = G5::parseOptionalParameters($params[1]);
            if(!isset($optionals['action'])){
                $optionals['action'] = 'upd';
                $params[1] = G5::computeOptionalParametersString($optionals);
            }
        }
        else{
            $msg = add::getErrorMessage();
            $msg = str_replace('to add', 'to update', $msg);
            $msg = str_replace('bc add', 'bc update', $msg);
            $msg = str_replace('action=add', 'action=upd', $msg);
            return $msg;
        }
        return add::execute($params);
    }
    
} // end class    
