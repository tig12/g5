<?php
/******************************************************************************

    @license    GPL
    @history    2019-11-16 03:37:58+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\csicop\si42;

use g5\Config;

class SI42{
    
    CONST TMP_FIELDS = [
        'FNAME',
        'GNAME',
        'DATE',
        'C2',
        'MA12', // mars, 12 sectors
        'SC',   // selected champion
    ];
    
    // ******************************************************
    public static function raw_filename(){
        return Config::$data['dirs']['1-si-raw'] . DS . 'si42' . DS . 'si42-p62-65.txt';
    }
    
    // ******************************************************
    public static function tmp_filename(){
        return Config::$data['dirs']['5-csicop'] . DS . '408-csicop-si42.csv';
    }
    
}// end class
