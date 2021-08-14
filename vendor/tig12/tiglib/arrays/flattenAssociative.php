<?php
/******************************************************************************
    Converts a nested associative array to a one-level associative array.
    Keys of the resulting arrays are a concatenation (separated by a character) of the nested keys.
    Ex: an array with this structure:
      id: 12
      name:
        family: Adam
        given: Juliette
        official:
          family: Lambert
    is converted to 
      id: 12
      name.family: Adam
      name.given: Juliette
      name.official.family: Lambert
    
    @license    GPL
    @history    2021-08-13 09:12:35+02:00, Thierry Graff : Creation
********************************************************************************/
namespace tiglib\arrays;

class flattenAssociative {
    
    /**
        Fills a csv file to an array of regular arrays.
        @param      $array The nested array to unnest
        @param      $sep Separator used to build the resulting keys.
        @return     The converted array.
    **/
    public static function compute($array, $sep='.'){
        $res = [];
        foreach($array as $k => $v){
            if(is_array($v)){
                $res = $res + self::aux($v, $k, $sep);
            }
            else{
                $res[$k] = $v;
            }
        }
        return $res;
    }
    
    /**
        @param  $base The base to be prepended to the resulting keys
    **/
    private static function aux($array, $base, $sep) {
        $res = [];
        foreach($array as $k => $v){
            if(is_array($v)){
                $res = $res + self::aux($v, "$base$sep$k", $sep); // recursive here
            }
            else{
                $res["$base$sep$k"] = $v;
            }
        }
        return $res;
    }
    
} // end class
