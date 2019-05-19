<?php
/******************************************************************************

    @license    GPL
    @history    2019-05-16 12:32:33+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\model;

use g5\init\Config;

class Full{
    
    /** Pattern to check birth date. **/
    const PDATE = '/\d{4}-\d{2}-\d{2}/';
    
    // ******************************************************
    /**
        Returns the path to sub-directory of 5-tmp/full corresponding too $birthdate
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The path or false if $birthdate is not formatted correctly.
    **/
    public static function getDirectory($birthdate){
        if(preg_match(self::PDATE, $birthdate) != 1){
            throw new \Exception("Invalid date : $birthdate"); 
        }
        //$subdir = substr($birthdate, 0, 10); // YYYY-MM-DD
        $subdir = substr($birthdate, 0, 4); // YYYY
        //$subdir = floor($y / 10) * 10; // one subdir per decade
        return Config::$data['dirs']['5-full'] . DS . $subdir;
    }
    /* 
        // test
        $dates = ['2018-12-21', '1790-12-12', '1792-12-12'];
        foreach($dates as $date){
            echo $date . ' : ' . Full::getDirectory($date) . "\n";
        }
    
    */
    
    // ******************************************************
    /**
        Returns the path to a file where a person is stored in 5-tmp/full
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The full path or false if $birthdate is not formatted correctly.
    **/
    public static function getFile($name, $fname, $gname, $birthdate){
        $slug = self::personSlug($name, $fname, $gname, $birthdate);
        $dir = self::getDirectory($birthdate);
        return $dir . DS . $slug . '.yml';
    }
    
    // ******************************************************
    /**
        
        @param $
    **/
    public static function personSlug($name, $fname, $gname, $birthdate): string{
        $slug = '';
        $bd = substr($birthdate, 0, 10);
        if($fname && $gname){
            return \lib::slugify("$fname-$gname-$bd");
        }
        if($name){
            return \lib::slugify("$name-$bd");
        }
        if($fname){
            return \lib::slugify("$fname-$bd");
        }
        if($gname){
            return \lib::slugify("$fname-$bd");
        }
        else{
            throw new \Exception("CANNOT COMPUTE SLUG :\n    name = $name\n    fname = $fname\n    gname = $gname\n    birthdate = $birthdate");
        }
    }
    
    
    // ******************************************************
    /**
        @param $
        @return false or an assoc array containing 2 elements :
                'filename'  : path to the matched yaml file
                'person'    : array contained in the yaml
    **/
    public static function matchArray($a){
        // @todo use php 7.3 syntax
        if(isset($a['DATE'])){
            $date = $a['DATE'];
        }
        else if(isset($a['BDATE'])){
            $date = $a['BDATE'];
        }
        else{
            return false;
        }
        $date = substr($date, 0, 10);
        $name = $a['NAME'] ?? '';
        $fname = $a['FNAME'] ?? '';
        $gname = $a['GNAME'] ?? '';
        $filename = self::getFile($name, $fname, $gname, $date);
        $dir = self::getDirectory($date);
//echo "$dir\n";
        // try match by filename
        $candidates = glob($dir . DS . "*$date*");
        if(in_array($filename, $candidates)){
            return yaml_parse(file_get_contents($filename));
        }
        return false;
//echo "$filename\n";
//echo "\n"; print_r($candidates); echo "\n";
    }
    
}// end class
