<?php
/********************************************************************************
    Constants and utilities related to AFD (Müller's Astro-Forschungs-Daten booklets).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-19 15:31:36+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller;

class AFD {
    
    /**
        Trust level for data coming from Astro-Forschungs-Daten booklets.
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /**
        Path to the yaml file containing the characteristics of Arno Müller.
        Relative to directory data/db/source
    **/
    const SOURCE_DEFINITION_FILE = 'muller' . DS . 'afd.yml';
    
    /**
        Slug of Astro-Forschungs-Daten source.
    **/
    const SOURCE_SLUG = 'afd';
    
    // ****************************************************
    // Code shared by several AFD files.    
    // ****************************************************
    
    /** 
        Common to m2men (famous men) and m3women (famous women).
        @param  $str String like '010 E 19'.
        @return Longitude expressed in decimal degrees.
    **/
    public static function computeLg($str): float {
        // use preg_split instead of explode(' ', $str) because of strings like
        // '005 E  2' (instead of '005 E 02')
        $tmp = preg_split('/\s+/', $str);
        $res = $tmp[0] + $tmp[2] / 60;
        $res = $tmp[1] == 'W' ? -$res : $res;
        return round($res, 2);
    }
    
    /** 
        Common to m2men (famous men) and m3women (famous women).
        @param  $str String like '50 N 59'.
        @return Latitude expressed in decimal degrees.
    **/
    public static function computeLat($str): float {
        $tmp = explode(' N ', $str);
        $multiply = 1;
        if(count($tmp) != 2){
            $tmp = explode(' S ', $str);
            $multiply = -1;
        }
        return $multiply * round($tmp[0] + $tmp[1] / 60, 2);
    }
    
    /** 
        Common to m2men (famous men) and m3women (famous women).
        @param  $str String like '21.30'.
        @return String like '21:30'.
    **/
    public static function computeHour($hour): string {
        return str_replace('.', ':', $hour);
    }
    
    /** 
        Common to m2men (famous men) and m3women (famous women).
        @param  $str String like '23.01.1840'.
        @return String like '1840-01-23'.
    **/
    public static function computeDay($str): string {
        $tmp = explode('.', $str);
        if(count($tmp) != 3){
            echo "ERROR DAY $str\n";
            return $str;
        }
        return implode('-', [$tmp[2], $tmp[1], $tmp[0]]);
    }
    
    /** 
        Common to m2men (famous men) and m3women (famous women).
        @param  $str String like '-0.83'.
        @return String like '00:50'.
    **/
    public static function computeTimezoneOffset($str): string {
        if($str == ''){
            return '';
        }
        preg_match('/(-?)(\d+)\.(\d+)/', $str, $m);
        array_shift($m);
        [$sign1, $hour1, $min1] = $m;
        // Müller's sign is inverse of ISO 8601
        $sign = $sign1 == '' ? '-' : '+';
        if((int)$hour1 == 0 && (int)$min1 == 0){
            $sign = '+';
        }
        $hour = str_pad($hour1, 2, '0', STR_PAD_LEFT);
        // $min1 is a decimal fraction of hour
        $min = round($min1 * 0.6); // *60 / 100
        $min = str_pad($min, 2, '0', STR_PAD_LEFT);
        $res = "$sign$hour:$min";
        return $res;
    }
    
} // end class
