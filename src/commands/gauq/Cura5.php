<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-10 09:57:43+02:00, Thierry Graff : creation from a split
********************************************************************************/
namespace g5\commands\gauq;

use g5\app\Config;
use tiglib\arrays\csvAssociative;
use g5\commands\gauq\LERRCP;

class Cura5 {
    
    /**
        Path to the yaml file containing the characteristics of "cura5" source.
        Relative to directory data/db
    **/
    const SOURCE_DEFINITION_FILE = 'cura5.yml';
    
    /**
        Slug of "cura5" source.
    **/
    const SOURCE_SLUG = 'cura5';
    
    /**
        Default trust level for data coming from Cura
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /** Separator used in raw (html) files **/
    const HTML_SEP = "\t";
    
    /** 
        For each line :
            - nb of records claimed by Cura
            - nb of records stored by g5
            - label on Cura web site
            - explanation of the difference between Cura and g5 numbers
        @see    LERRCP::LERRCP_INFOS
    **/
    const CURA_CLAIMS = [
        'A1' =>  [2088, 2087, 'Y, see <a href="https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA1y.html">Cura web site</a>'],
        'A2' =>  [3644, 3643, 'Y, see <a href="https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA2y.html">Cura web site</a>'],
        'A3' =>  [3047, 3046,  'N'],
        'A4' =>  [2722, 2720,  'N'],
        'A5' =>  [2412, 2410, 'N'],
        'A6' =>  [2027, 2026, 'N'],
        'D6' =>  [450,  449, 'N'],
        'D10' => [1398, 1396, 'N'],
        'E1' =>  [2154, 2153, 'N'],
        'E3' =>  [1540, 1539, 'N'],
    ];
    
    /** 
        URLs of the raw files in Cura web site
    **/
    const CURA_URLS = [
        'A1' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA1y.html',
        'A2' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA2y.html',
        'A3' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA3y.html',
        'A4' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA4y.html',
        'A5' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA5y.html',
        'A6' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdA6y.html',
        'D6' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdD6.html',
        'D10' => 'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdD10.html',
        'E1' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdE1.html',
        'E3' =>  'https://web.archive.org/web/20200916073026/http://cura.free.fr/gauq/902gdE3.html',
    ];
    
    // *********************** Time / space functions ***********************
    
    /**
        Converts the fields YEA, MON, DAY of a line in a YYYY-MM-DD date
    **/
    public static function computeDay($array){
        return trim($array['YEA']) . '-' . sprintf('%02s', trim($array['MON'])) . '-' . sprintf('%02s', trim($array['DAY']));
    }
    
    /**
        Converts the fields H, MN, SEC of a line in a HH:MM:SS hour
        @param  $array Associative array containing 3 fields : H, MN, SEC
    **/
    public static function computeHHMMSS($array){
        return trim(sprintf('%02s', $array['H']) . ':' . sprintf('%02s', $array['MN']) . ':' . sprintf('%02s', $array['SEC']));
    }
    
    /**
        Converts the fields H, MN of a line in a HH:MM hour
        @param  $array Associative array containing 2 fields : H, MN
    **/
    public static function computeHHMM($array){
        return trim(sprintf('%02s', $array['H']) . ':' . sprintf('%02s', $array['MN']));
    }
    
    /**
        Converts field LON in decimal degrees
    **/
    public static function computeLg($str){
        preg_match('/(\d+)(E|W) *?(\d+)/', $str, $m);
        if(count($m) != 4){
            throw new \Exception("Unable to parse longitude : <b>$str</b>");
        }
        $res = ($m[1] + $m[3] / 60 ) * ($m[2] == 'E' ? 1 : -1);
        return round($res, 5);
    }

    /**
        Converts field LAT in decimal degrees
    **/
    public static function computeLat($str){
        preg_match('/(\d+)(N|S) *?(\d+)/', $str, $m);
        if(count($m) != 4){
            throw new \Exception("Unable to parse latitude : <b>$str</b>");
        }
        $res = ($m[1] + $m[3] / 60 ) * ($m[2] == 'N' ? 1 : -1);
        return round($res, 5);
    }


} // end class
