<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2019-05-10 09:57:43+02:00, Thierry Graff : creation from a split
********************************************************************************/
namespace g5\commands\cura;

use g5\Config;
use g5\model\DB5;
use g5\model\Source;
use tiglib\arrays\csvAssociative;

class Cura{
    
    /** uid when cura is used to create a group **/
    const UID_PREFIX_GROUP = 'group' . DB5::SEP . 'datasets' . DB5::SEP . 'cura';
    
    /** uid when cura is used to create a source **/
    const UID_PREFIX_SOURCE = 'source' . DB5::SEP . 'cura';
    
    /** Slug of the source cura **/
    const SOURCE_SLUG = 'cura';
    
    /**
        @see trust levels https://tig12.github.io/gauquelin5/check.html
    **/
    const TRUST_LEVEL = 3;
    
    /** Separator used in raw (html) files **/
    const HTML_SEP = "\t";
    
    /** 
        Possible values of parameter indicating the subject to process.
    **/
    const DATAFILES_POSSIBLES = [
        'all',
        'A', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
        // 'B', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
        'D6', 'D10',
        'E1', 'E3',
    ];
    
    /**
        Names of the columns in files of 5-cura-csv/
        @todo Problem with this constant :
              It corresponds to fields of serie A
              For example, different for E1 E3
              So should not be in class Cura, but in class cura\A\A
    **/
    const TMP_CSV_COLUMNS = [
            'NUM',
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'DATE_C',
            'PLACE',
            'CY',
            'C2',
            'LG',
            'LAT',
            'GEOID',
            'NOTES',
        ];

    
    /** 
        Associations between datafile in the user's vocabulary and the sub-namespace that handles it.
        (sub-namespace of g5\commands\cura).
        @todo Put this constant in CuraRouter ?
    **/
    const DATAFILES_SUBNAMESPACE = [
        'all' => 'all',
        'A' => 'A',
        'A1' => 'A',
        'A2' => 'A',
        'A3' => 'A',
        'A4' => 'A',                                                                             
        'A5' => 'A',
        'A6' => 'A',
        'D6' => 'D6',
        'D10' => 'D10',
        'E1' => 'E1_E3',
        'E3' => 'E1_E3',
    ];
    
    
    // ================================= source management =================================
    
    // ******************************************************
    /** Returns a source for cura **/
    public static function getSource(): Source {
        $source = Source::newEmpty();
        $uid = UID_PREFIX_SOURCE . DB5::SEP . self::SOURCE_SLUG;
        $file = str_replace(DB5::SEP, DS, $uid) . '.yml';
        $source->data = [
            'uid' => $uid,
            'slug' => self::SOURCE_SLUG,
            'file' => $file,
            'name' => "CURA",
            'description' => "Web site cura.free.fr",
        ];
        return $source;
    }
    
    
    // ================================= Code used by import =================================
    
    // ******************************************************
    /**
        Returns a Gauquelin id, like "A1-654"
        Unique id of a record among cura files.
        @param $datafile    String like 'A1'
        @param $NUM         Value of field NUM of a record within $datafile
    **/
    public static function gqid($datafile, $NUM){
        return "$datafile-$NUM";
    }
    
    // ******************************************************
    /**
        Returns the name of a file in 5-cura-csv/
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpFilename($datafile){
        return Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
    }
    
    // ******************************************************
    /**
        Loads a cura file of 5-cura-csv/ in a regular array
        @param  $datafile : a string like 'A1'
        @return Regular array containing the persons' data
    **/
    public static function loadTmpCsv($datafile){
        return csvAssociative::compute(Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv');
    }

    // ******************************************************
    /**
        Loads a cura file of 5-cura-csv/ in an asssociative array ; keys = cura ids (NUM)
        @param      $datafile : a string like 'A1'
        @return     Associative array containing the cura file in 5-cura-csv/ ; keys = cura ids (NUM)
    **/
    public static function loadTmpCsv_num($datafile){
        $curaRows1 = self::loadTmpCsv($datafile);
        $res = [];              
        foreach($curaRows1 as $row){
            $res[$row['NUM']] = $row;
        }
        return $res;
    }
    
    // *****************************************
    /** 
        Computes the name of a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr (see absolute path of this directory in config.yml)
        @param  $datafile : a string like 'A1'
        @return filename, a string like '902gdA1y.html' or '902gdB1.html'
    **/
    public static function rawFilename($datafile){
        return '902gd' . $datafile . (substr($datafile, 0, 1) == 'A' ? 'y' : '') . '.html';
    }
    
    // *****************************************
    /** 
        Reads a html file downloaded from cura.free.fr
        and locally stored in directory data/raw/cura.free.fr (see absolute path of this directory in config.yml)
        @param  $datafile : string like 'A1'
        @return The content of the file
        
        @todo This code is specific to cura.free.fr data source => should be in cura.free.fr importer
    **/
    public static function readHtmlFile($datafile){
        $raw_file = Config::$data['dirs']['1-cura-raw'] . DS . self::rawFilename($datafile);
        $tmp = @file_get_contents($raw_file);
        if(!$tmp){
            $msg = "ERROR : Unable to read file $raw_file\n"
                . "Check that config.yml indicates a correct path\n";
            throw new \Exception($msg);
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
