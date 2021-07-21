<?php
/********************************************************************************
    Constants and utilities shared by several classes of this package.
    
    @license    GPL
    @history    2019-05-10 09:57:43+02:00, Thierry Graff : creation from a split
********************************************************************************/
namespace g5\commands\cura;

use g5\Config;
use g5\model\Source;
use g5\model\Group;
use tiglib\arrays\csvAssociative;

class Cura {
    
    /**
        Path to the yaml file containing the characteristics of "cura5" source.
        Relative to directory data/model
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
    **/
    const CURA_CLAIMS = [
        'A1' =>  [2088, 2087, '2088 sports champions', 'Y, see <a href="http://cura.free.fr/gauq/902gdA1y.html">Cura web site</a>'],
        'A2' =>  [3644, 3643, '3644 scientists and medical doctors', 'Y, see <a href="http://cura.free.fr/gauq/902gdA2y.html">Cura web site</a>'],
        'A3' =>  [3047, 3046, '3047 military men', 'N'],
        'A4' =>  [2722, 2720, '1473 painters and 1249 French musicians', 'N'],
        'A5' =>  [2412, 2410, '1409 actors and 1003 politicians', 'N'],
        'A6' =>  [2027, 2026, '2027 writers and journalists', 'N'],
        'D6' =>  [450,  449, '450 New famous European Sports Champions', 'N'],
        'D10' => [1398, 1396, '1398 data of successful Americans', 'N'],
        'E1' =>  [2154, 2153, '2154 French Physicians, Military Men and Executives', 'N'],
        'E3' =>  [1540, 1539, '1540 New French Writers, Artists, Actors, Politicians and Journalists', 'N'],
    ];
    
    /** 
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
        Informations about the different LERRCP booklets.
        Each line contains:
            - date of publication
            - nb of pages (empty sting when unknown)
            - array of author names
        Source http://cura.free.fr/gauq/902gdG.html
        For documentation purpose only.
    **/
    const LERRCP_INFOS = [
        'A1' =>  ['1970-04', '',  ['Michel Gauquelin', 'Françoise Gauquelin']],
        'A2' =>  ['1970-05', 150, ['Michel Gauquelin', 'Françoise Gauquelin']],
        'A3' =>  ['1970-07', '',  ['Michel Gauquelin', 'Françoise Gauquelin']],
        'A4' =>  ['1970-11', 119, ['Michel Gauquelin', 'Françoise Gauquelin']],
        'A5' =>  ['1970-12', '',  ['Michel Gauquelin', 'Françoise Gauquelin']],
        'A6' =>  ['1971-03', 123, ['Michel Gauquelin', 'Françoise Gauquelin']],
        'D6' =>  ['1979-09', '',  ['Michel Gauquelin', 'Françoise Gauquelin']],
        'D10' => ['1982-01', '',  ['Michel Gauquelin']],
        'E1' =>  ['1984',    '',  ['Michel Gauquelin']],
        'E3' =>  ['1984',    '',  ['Michel Gauquelin']],
    ];
    
    // *********************** Person ids ***********************
    /**
        Returns a unique Gauquelin id, like "A1-654"
        Unique id of a record among birth dates published by Gauquelin's LERRCP.
        See https://tig12.github.io/gauquelin5/cura.html for precise definition.
        @param $datafile    String like 'A1'
        @param $NUM         Value of field NUM of a record within $datafile
    **/
    public static function gqid($datafile, $NUM){
        return "$datafile-$NUM";
    }
    
    // *********************** Source management ***********************
    
    /**
        Computes slug of the source corresponding to cura page of a datafile.
        Ex: for datafile 'A6', return 'a6'
    **/
    public static function datafile2sourceSlug($datafile) {
        return strtolower($datafile);
    }
    
    /**
        Computes slug of the source corresponding to cura page of a datafile.
        Ex: for datafile 'A6', return 'a6-booklet'
    **/
    public static function datafile2bookletSourceSlug($datafile) {
        return self::datafile2sourceSlug($datafile) . '-booklet';
    }
    
    /**
        Returns a Source object for one file of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getSourceOfFile($datafile): Source {
        $source = new Source();
        $slug = self::datafile2sourceSlug($datafile);
        $source->data['slug'] = $slug;
        $source->data['name'] = "CURA5 file $datafile";
        $source->data['type'] = 'file';
        $source->data['authors'] = ['Patrice Guinard'];
        $source->data['description'] = 'Web page ' . self::CURA_URLS[$datafile]
            . "\nDescribed by Cura as " . self::CURA_CLAIMS[$datafile][2];
        $source->data['parents'][] = self::datafile2bookletSourceSlug($datafile);
        $source->data['parents'][] = self::SOURCE_SLUG;
        return $source;
    }
    
    /**
        Returns a Source object corresponding to original Gauquelin booklet
        for one file of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getBookletSourceOfFile($datafile): Source {
        $source = new Source();      
        $source->data['slug'] = self::datafile2bookletSourceSlug($datafile);
        $source->data['name'] = "LERRCP $datafile";
        $source->data['type'] = 'booklet';
        $source->data['authors'] = self::LERRCP_INFOS[$datafile][2];
        $serie = substr($datafile, 0, 1);
        $volume = substr($datafile, 1);
        $source->data['description'] = "LERRCP Serie $serie, vol $volume: "
            . self::CURA_CLAIMS[$datafile][2]
            . "\nPublished in " . self::LERRCP_INFOS[$datafile][0];
        if(self::LERRCP_INFOS[$datafile][1] != ''){
            $source->data['description'] .= ' (' . self::LERRCP_INFOS[$datafile][1] . ' pages)';
        }
        $source->data['parents'] = ['lerrcp'];
        return $source;
    }
    
    // *********************** Group management ***********************
    
    /**
        Computes slug of the source corresponding to cura page of a datafile.
        Ex: for datafile 'A6', return 'a6'
    **/
    public static function datafile2groupSlug($datafile) {
        return strtolower($datafile);
    }
    /**
        Returns a Source object for one file of cura web site.
        @param  $datafile : string like 'A1'
    **/
    public static function getGroupOfFile($datafile): Group {
        $g = new Group(); 
        $g->data['slug'] = self::datafile2groupSlug($datafile);
        $g->data['name'] = "Gauquelin $datafile";
        $g->data['description'] = "According to Gauquelin : " . Cura::CURA_CLAIMS[$datafile][2] . ".\n"
            . "In practice, contains " . Cura::CURA_CLAIMS[$datafile][1] . " persons.";
        $g->data['id'] = $g->insert();
        return $g;
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
        Returns the name of a tmp file, eg. data/tmp/cura/A1.csv
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpDirname(){
        return Config::$data['dirs']['tmp'] . DS . 'cura';
    }
    
    /**
        Returns the name of a tmp file, eg. data/tmp/cura/A1.csv
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpFilename($datafile){
        return self::tmpDirname() . DS . $datafile . '.csv';
    }
    
    /**
        Loads a tmp file in a regular array
        Each element contains the person fields in an assoc. array
        @param  $datafile : a string like 'A1'
    **/
    public static function loadTmpFile($datafile){
        return csvAssociative::compute(self::tmpFilename($datafile));
    }

    /**
        Loads a tmp file in an asssociative array.
            keys = cura ids (NUM)
            values = assoc array containing the fields
        @param      $datafile : a string like 'A1'
    **/
    public static function loadTmpFile_num($datafile){
        $curaRows1 = self::loadTmpFile($datafile);
        $res = [];
        foreach($curaRows1 as $row){
            $res[$row['NUM']] = $row;
        }
        return $res;
    }
    
    // *********************** Tmp raw files manipulation ***********************
    
    /**
        Returns the name of a "tmp raw file", eg. data/tmp/cura/A1-raw.csv
        (files used to keep trace of the original raw values).
        @param  $datafile : a string like 'A1'
    **/
    public static function tmpRawFilename($datafile){
        return Config::$data['dirs']['tmp'] . DS . 'cura' . DS . $datafile . '-raw.csv';
    }
    
    /**
        Loads a "tmp raw file" in a regular array
        Each element contains the person fields in an assoc. array
        @param  $datafile : a string like 'A1'
    **/
    public static function loadTmpRawFile($datafile){
        return csvAssociative::compute(self::tmpRawFilename($datafile));
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
