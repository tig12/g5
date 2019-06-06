<?php
/******************************************************************************
    Utilities for names.
    
    @license    GPL
    @history    2019-06-06 22:34:27+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Names{
    
    
    // ******************************************************
    /**
        If a string contains two words separated by a space,
        explodes it to split family name from given name.
        Else it puts $str in family name and leaves given name empty.
        @return Array with 2 strings : family and given.
    **/
    public static function familyGiven($str){
        $gname = '';
        $tmp = explode(' ', $str);
        if(count($tmp) == 2){
            $fname = $tmp[0];
            $gname = $tmp[1];
        }
        else{
            $fname = $str;
        }
        return [$fname, $gname];
    }
    
    
}// end class
