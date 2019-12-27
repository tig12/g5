<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

package g5\model;

use g5\Config;                                                      
use tiglib\strings\slugify;

class Person{

    // ******************************************************
    /**
        @param $
    **/
    public static function new(&$data=[], $params=[]){
        $file = self::filename($data);
    }
    
    
    // ******************************************************
    /**
        Returns the path to a file where a person is stored in 5-tmp/full
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The full path or false if $birthdate is not formatted correctly.
    **/
    public static function filename(&$data=[]){
        $slug = self::slug($data['name'], $data['birthdate']);
        $dir = self::dirname($data['birthdate']);
        return $dir . DS . $slug . '.yml';
    }
    
    // ******************************************************
    /**
        Returns the path to sub-directory of 5-tmp/full corresponding too $birthdate
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The path or false if $birthdate is not formatted correctly.
    **/
    public static function dirname($birthdate){
        if(preg_match(self::PDATE, $birthdate) != 1){
            return Full::$DIR . DS . 'lost'; 
        }
        $date = substr($birthdate, 0, 10);
        [$y, $m, $d] = ecplode('-', $date);
        return implode(DS, [Full::$DIR, $y, $m, $d]);
    }
    
    
    // ******************************************************
    /**
        
        @param $
    **/
    public static function slug($name, $fname, $gname, $birthdate): string{
        $slug = '';
        $bd = substr($birthdate, 0, 10);
        if($fname && $gname){
            return slugify::compute("$fname-$gname-$bd");
        }
        if($name){
            return slugify::compute("$name-$bd");
        }
        if($fname){
            return slugify::compute("$fname-$bd");
        }
        if($gname){
            return slugify::compute("$fname-$bd");
        }
        else{
            throw new \Exception("CANNOT COMPUTE SLUG :\n    name = $name\n    fname = $fname\n    gname = $gname\n    birthdate = $birthdate");
        }
    }
    
    
    
}// end class
