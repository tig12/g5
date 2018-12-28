<?php
/********************************************************************************
    Importation of Gauquelin 5th edition
    Main class, conducts the computation
    
    @license    GPL
    @history    2017-04-27 10:41:02+02:00, Thierry Graff : creation
********************************************************************************/
namespace gauquelin5;

use gauquelin5\init\Config;

class Gauquelin5{
    
    /** Separator used in original (html) files **/
    const HTML_SEP = "\t";
    
    /** Separator used in the generated (csv) files **/
    const CSV_SEP = ';';
    
    /** Associations between series and class names **/
    const SERIES_CLASS = [
        'A1' => 'SerieA',
        'A2' => 'SerieA',
        'A3' => 'SerieA',
        'A4' => 'SerieA',
        'A5' => 'SerieA',
        'A6' => 'SerieA',
        //
        '1955' => 'Serie1955',
        //
        'B1' => 'SerieB',
        'B2' => 'SerieB',
        'B3' => 'SerieB',
        'B4' => 'SerieB',
        'B5' => 'SerieB',
        'B6' => 'SerieB',
        //
        'D6' => 'SerieD6',
        'D9a' => '',
        'D9b' => '',
        'D9c' => '',
        'D10' => 'SerieD10',
        //
        'E1' => 'SerieE1_E3',
        //
        'E2' => '',
        'E2a' => '',
        'E2b' => '',
        'E2c' => '',
        'E2d' => '',
        'E2e' => '',
        'E2f' => '',
        'E2g' => '',
        //
        'E3' => 'SerieE1_E3',
        'F1' => '',
        'F2' => '',
    ];
    
    // ******************************************************
    /** 
        Unique entry point of this package
        Acts as a router to different action methods
        @param      $serie string representing a valid serie (as defined in run-gauquelin5.php)
        @param      $action string representing a valid action (as defined in run-gauquelin5.php)
        @return     string a report
    **/
    public static function action($action, $serie){
        switch($serie){
        	case 'A' : 
        	    $series = ['A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
        	break;
        	case 'B' : 
        	    $series = ['B1', 'B2', 'B3', 'B4', 'B5', 'B6'];
        	break;
            default:
                $series = [$serie];
            break;
        }
        $report = '';
        foreach($series as $s){
            $classname = 'gauquelin5\\' . self::SERIES_CLASS[$s];
            $report .= $classname::$action($s);
        }
        return $report;
    }
    
    
    // *****************************************
    /** 
        Computes the name of a html file downloaded from cura.free.fr
        and locally stored in directory 1-cura-raw (see absolute path of this directory in config.yml)
        @param  $serie : a string like 'A1'
        @return filename, a string like '902gdA1.html'
    **/
    public static function serie2filename($serie){
        return '902gd' . $serie . (substr($serie, 0, 1) == 'A' ? 'y' : '') . '.html';
    }
    
    
    // *****************************************
    /** 
        Reads a html file downloaded from cura.free.fr
        and locally stored in directory 1-cura-raw (see absolute path of this directory in config.yml)
        @param  $serie : string like 'A1'
        
        @todo This code is specific to cura.free.fr data source => should be in cura.free.fr importer
    **/
    public static function readHtmlFile($serie){
        $raw_file = Config::$data['dirs']['1-cura-raw'] . DS . self::serie2filename($serie);
        return utf8_encode(file_get_contents($raw_file));
    }
    
    
    // *****************************************
    /**
        Converts the fields YEA, MON, DAY of a line in a YYYY-MM-DD date
    **/
    public static function computeDay($array){
        return trim($array['YEA']) . '-' . sprintf('%02s', trim($array['MON'])) . '-' . sprintf('%02s', trim($array['DAY']));
    }
    
    
    // *****************************************
    /**
        Converts the fields H, MN, SEC of a line in a HH:MM:SS hour
    **/
    public static function computeHour(&$array){
        return trim(sprintf('%02s', $array['H']) . ':' . sprintf('%02s', $array['MN']) . ':' . sprintf('%02s', $array['SEC']));
    }
    
    
    // *****************************************
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

    
    // *****************************************
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


}// end class

