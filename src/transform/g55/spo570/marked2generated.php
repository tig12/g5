<?php
/********************************************************************************
    Alias to g5\transform\g55\all\marked2generated
    Called with :
    php run.php g55 spo570 marked2generated
    
    @license    GPL
    @history    2019-05-28 00:56:51+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\g55\spo570;

use g5\init\Config;
use g5\patterns\Command;

class marked2generated implements Command {
    
    // *****************************************
    /** 
        @return report
    **/
    public static function execute($params=[]): string{
        return \g5\transform\g55\all\marked2generated::execute('570SPO');
    }
    
}// end class
