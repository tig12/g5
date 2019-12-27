<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 05:50:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\Config;
use tiglib\strings\slugify;

Full::init();
class Full{
    
    /** Pattern to check birth date. **/
    const PDATE = '/\d{4}-\d{2}-\d{2}/';
    
    public static $DIR;
    
    // ******************************************************
    /**
        @param $
    **/
    public static function init(){
        self::$DIR = Config::$data['dirs']['7-full'];
    }
    
}// end class
