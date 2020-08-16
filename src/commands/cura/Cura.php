<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2019-05-10 09:57:43+02:00, Thierry Graff : creation from a split
********************************************************************************/
namespace g5\commands\cura;

use g5\Config;
use g5\model\SourceI;
use g5\model\Source;
use tiglib\arrays\csvAssociative;

class Cura implements SourceI {
    
    /**
        Path to the yaml file containing the characteristics of the source.
        Relative to directory specified in config.yml by dirs / edited
    **/
    const SOURCE_DEFINITION = 'source' . DS . 'web' . DS . 'cura.yml';
    
    /**
        Trust level for data coming from Cura
        @see https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 4;
    
    /** Separator used in raw (html) files **/
    const HTML_SEP = "\t";
    
    /** 
        For documentation purpose only
        For each line :
            - nb of records claimed by Cura
            - label on Cura web site
            - explanation of the difference between Cura and g5 numbers
    **/
    const CURA_CLAIMS = [
        'A1' =>  [2088, '2088 sports champions', 'Y, see <a href="http://cura.free.fr/gauq/902gdA1y.html">Cura web site</a>'],
        'A2' =>  [3644, '3644 scientists and medical doctors', 'Y, see <a href="http://cura.free.fr/gauq/902gdA2y.html">Cura web site</a>'],
        'A3' =>  [3047, '3047 military men', 'N'],
        'A4' =>  [2722, '1473 painters and 1249 French musicians', 'N'],
        'A5' =>  [2412, '1409 actors and 1003 politicians', 'N'],
        'A6' =>  [2027, '2027 writers and journalists', 'N'],
        'D6' =>  [450,  '450 New famous European Sports Champions', 'N'],
        'D10' => [1398, '1398 data of successful Americans', 'N'],
        'E1' =>  [2154, '2154 French Physicians, Military Men and Executives', 'N'],
        'E3' =>  [1540, '1540 New French Writers, Artists, Actors, Politicians and Journalists', 'N'],
    ];
    
    /** 
        For documentation purpose only
        URLs of the raw files in Cura web site
    **/
    const CURA_URLS = [
        'A1' =>  'http://cura.free.fr/gauq/902gdA1y.html',
        'A2' =>  'http://cura.free.fr/gauq/902gdA2y.html',
        'A3' =>  'http://cura.free.fr/gauq/902gdA3y.html',
        'A4' =>  'http://cura.free.fr/gauq/902gdA4y.html',
        'A5' =>  'http://cura.free.fr/gauq/902gdA5y.html',
        'A6' =>  'http://cura.free.fr/gauq/902gdA6y.html',
        'D6' =>  'http://cura.free.fr/gauq/902gdD6.html',
        'D10' => 'http://cura.free.fr/gauq/902gdD10.html',
        'E1' =>  'http://cura.free.fr/gauq/902gdE1.html',
        'E3' =>  'http://cura.free.fr/gauq/902gdE3.html',
    ];
    
    /**
        Returns a Gauquelin id, like "A1-654"
        Unique id of a record among cura files.
        @param $datafile    String like 'A1'
        @param $NUM         Value of field NUM of a record within $datafile
    **/
    public static function gqid($datafile, $NUM){
        return "$datafile-$NUM";
    }
    
    // *********************** Source management ***********************
    
    /** Returns a Source object for cura web site. **/
    public static function getSource(): Source {
        return Source::getSource(Config::$data['dirs']['edited'] . DS . self::SOURCE_DEFINITION);           
    }
    
    // *********************** Raw files manipulation ***********************
    
    /** 
        Computes the name of the directory where raw cura files are stored
    **/
    public static function rawDirname(){
        return Config::$data['dirs']['raw'] . DS . 'cura.free.fr';
    }
    
    /** 
        Computes the name of a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr
        @param  $datafile : a string like 'A1'
        @return filename, a string like '902gdA1y.html' or '902gdB1.html'
    **/
    public static function rawFilename($datafile){
        return '902gd' . $datafile . (substr($datafile, 0, 1) == 'A' ? 'y' : '') . '.html';
    }
    
    /** 
        Reads a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr
        @param  $datafile : string like 'A1'
        @return The content of the file
    **/
    public static function loadRawFile($datafile){
        $rawFile = self::rawDirname() . DS . self::rawFilename($datafile);
        $tmp = @file_get_contents($rawFile);
        if(!$tmp){
            $msg = "ERROR : Unable to read file $rawFile\n"
                . "Check that config.yml indicates a correct path\n";
            throw new \Exception($msg);
        }
        return utf8_encode($tmp);
    }
    
    // *********************** Tmp files manipulation ***********************
    
    /**
        Returns the name of a file in data/tmp/cura
        @param  $datafile : a string like 'A1'
    **/                                                                                          
    public static function tmpFilename($datafile){
        return Config::$data['dirs']['tmp'] . DS . 'cura' . DS . $datafile . '.csv';
    }
    
    /**
        Returns the name of a file in data/tmp/cura used to keep trace of the original raw values
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpRawFilename($datafile){
        return Config::$data['dirs']['tmp'] . DS . 'cura' . DS . $datafile . '-raw.csv';
    }
    
    /**
        Loads a cura file of data/tmp/cura in a regular array
        @param  $datafile : a string like 'A1'
        @return Regular array containing the persons' data
    **/
    public static function loadTmpFile($datafile){
        return csvAssociative::compute(self::tmpFilename($datafile));
    }

    /**
        Loads a cura file of data/tmp/cura in an asssociative array ; keys = cura ids (NUM)
        @param      $datafile : a string like 'A1'
        @return     Associative array containing the cura file in data/tmp/cura ; keys = cura ids (NUM)
    **/
    public static function loadTmpFile_num($datafile){
        $curaRows1 = self::loadTmpFile($datafile);
        $res = [];              
        foreach($curaRows1 as $row){
            $res[$row['NUM']] = $row;
        }
        return $res;
    }
    
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


}// end class
