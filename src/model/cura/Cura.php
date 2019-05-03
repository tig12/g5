<?php
/********************************************************************************
    Code common to all cura files.
    
    @license    GPL
    @history    2019-05-03 17:18:33+02:00, Thierry Graff : creation from a split of class Gauquelin5
********************************************************************************/
namespace gauquelin5\model\cura;

use gauquelin5\init\Config;

class Cura{
    
    /** Separator used in original (html) files **/
    const HTML_SEP = "\t";
    
    // *****************************************
    /** 
        Computes the name of a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr (see absolute path of this directory in config.yml)
        @param  $serie : a string like 'A1'
        @return filename, a string like '902gdA1y.html' or '902gdB1.html'
    **/
    public static function subject2filename($serie){
        return '902gd' . $serie . (substr($serie, 0, 1) == 'A' ? 'y' : '') . '.html';
    }
    
    
    // *****************************************
    /** 
        Reads a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr (see absolute path of this directory in config.yml)
        @param  $serie : string like 'A1'
        @return The content of the file
        
        @todo This code is specific to cura.free.fr data source => should be in cura.free.fr importer
    **/
    public static function readHtmlFile($serie){
        $raw_file = Config::$data['dirs']['1-cura-raw'] . DS . self::subject2filename($serie);
        $tmp = @file_get_contents($raw_file);
        if(!$tmp){
            echo "ERROR : Unable to read file $raw_file\n";
            echo "Check that config.yml indicates a correct path\n";
            exit;
        }
        return utf8_encode($tmp);
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
        @param  $array Associative array containing 3 fields : H, MN, SEC
    **/
    public static function computeHHMMSS($array){
        return trim(sprintf('%02s', $array['H']) . ':' . sprintf('%02s', $array['MN']) . ':' . sprintf('%02s', $array['SEC']));
    }
    
    // *****************************************
    /**
        Converts the fields H, MN of a line in a HH:MM hour
        @param  $array Associative array containing 2 fields : H, MN
    **/
    public static function computeHHMM($array){
        return trim(sprintf('%02s', $array['H']) . ':' . sprintf('%02s', $array['MN']));
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
