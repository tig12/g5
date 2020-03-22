<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2020-03-22 21:09:32+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\acts;

use g5\Config;
use tiglib\arrays\csvAssociative;

Acts::init();
class Acts{
    
    /** Directory containing birth acts **/
    public static $DIR;
    
    // ******************************************************
    /**
        @param $
    **/
    public static function init(){
        self::$DIR = Config::$data['dirs']['8-acts'] . DS . 'birth';
    }
}// end class
