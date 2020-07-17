<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2020-07-13 17:15:37+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch;

use g5\model\DB5;

Newalch::init();

class Newalch{
    
    /** uid when newalch is used to create a group **/
    const UID_PREFIX_GROUP = 'group' . DB5::SEP . 'datasets' . DB5::SEP . 'newalch';
    
    /** Path where groups members are stored **/
    public static $STORAGE_PATH;
    
    public static function init(){
        self::$STORAGE_PATH = DB5::$DIR . DS . 'tmp' . DS . 'group' . DS . 'datasets' . DS . 'newalch'; 
    }
    
}// end class
