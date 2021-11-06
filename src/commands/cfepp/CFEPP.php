<?php
/******************************************************************************

    CFEPP = Comité Français pour l’Étude des Phénomènes Paranormaux    

    @license    GPL
    @history    2021-11-06 20:51:55+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp;

use g5\app\Config;

class CFEPP {
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1996-cfepp';
    }
    
} // end class
