<?php
/******************************************************************************
    Utilities for french names.
    
    @license    GPL
    @history    2019-06-06 22:53:04+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Names_fr{
    
    public static $tr = [
        'Andre' => 'André',
        'Desire' => 'Désiré',           
        'Dieudonne' => 'Dieudonné',
        'Felix' => 'Félix',
        'Eugene' => 'Eugène',
        'Leon' => 'Léon',
        'Rene' => 'René',
        'Stephane' => 'Stéphane',
    ];
    
    // ******************************************************
    /**
        Fix accents in common given names; 
        @return     Corrected string
    **/
    public static function accentGiven($str): string{
        foreach(self::$tr as $k => $v){
            if($str == $k){
                $str = $v;
            }
        }
        return $str;
    }
    
    
}// end class
