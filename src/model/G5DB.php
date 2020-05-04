<?php
/******************************************************************************
    Gauquelin5 database
    @license    GPL
    @history    2019-12-27 05:50:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\Config;
use tiglib\strings\slugify;

G5DB::init();

class G5DB{
    
    /** Separator used to build uid **/
    const SEP = '/';
    
    /** Path pointing to 7-full **/
    public static $DIR;
    
    /** Pattern to check birth date. **/
    const PDATE = '/\d{4}-\d{2}-\d{2}/';
    
    // ******************************************************
    public static function init(){
        self::$DIR = Config::$data['dirs']['7-full'];
    }
    
}// end class
