<?php
/******************************************************************************
    Utilities for names.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-06 22:34:27+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

class Names{
    
    
    /**
        Separates family name from given name in a string in certain cases :
        - If the string contains two words separated by a space,
        - If the string contains 3 words separated by a space
          and the first word is (case insensitive) "de", "di", "dal", "del", "van", "le", "la", "ben"
        
        In these cases, explodes it to split family name from given name.
        
        Else it puts $str in family name and leaves given name empty.
        @return Array with 2 strings : family and given.
    **/
    public static function familyGiven($str){
        $fname = $str;
        $gname = '';
        $tmp = explode(' ', $str);
        if(count($tmp) == 2){
            $fname = $tmp[0];
            $gname = $tmp[1];
        }
        else if(count($tmp) == 3){
            $test = strtolower($tmp[0]);
            switch($test){
            	case 'de': 
            	case 'di': 
            	case 'dal': 
            	case 'del': 
            	case 'van': 
            	case 'le': 
            	case 'la': 
            	case 'ben': 
                    $fname = $tmp[0] . ' ' . $tmp[1];
                    $gname = $tmp[2];
            	break;
            }
        }
        else{
            $fname = $str;
        }
        return [ucfirst($fname), ucfirst($gname)];
    }
    
    
}// end class
