<?php
/******************************************************************************
                                                     
    CPara = "Comité para" = Comité belge pour l'investigation scientifique des phénomènes réputés paranormaux
                                   
    @license    GPL
    @history    2021-11-06 20:47:26+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cpara;

use g5\app\Config;

class CPara {
    
    /** 
        Computes the name of the directory where output files are stored
    **/
    public static function outputDirname(){
        return Config::$data['dirs']['output'] . DS . 'history' . DS . '1976-cpara';
    }
    
} // end class
