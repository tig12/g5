<?php
/********************************************************************************
    Shortcut to add command, which handles BC add and update.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-04-20 08:58:12+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\wiki\bc;

use tiglib\patterns\Command;

class update implements Command {
    
    /** 
        @param  $params Array containing one element: the slug of the person to add
                        ex: wiki bc add galois-evariste-1811-10-25
        @return String report
    **/
    public static function execute($params=[]): string{
        return add::execute($params);
    }
    
} // end class    
