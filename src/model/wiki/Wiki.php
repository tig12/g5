<?php
/********************************************************************************
    Constants and utilities related to Wiki management.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-12-24 15:54:45+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\model\wiki;
use g5\app\Config;

class Wiki {
    
    /**
        Slug of wiki information source.
    **/
    const SOURCE_SLUG = 'wiki';
    
    // *********************** File manipulation ***********************
    
    /**
        @return Path to the directory containing wiki data.
    **/
    public static function rootDir(){
        return Config::$data['dirs']['wiki'];
    }
    
    /**
        Computes the directory where person informations are stored, relative to Wiki root dir.
        @param  $slug The slug of the person to add ; ex: galois-evariste-1811-10-25
        @return The relative directory path ; 1811/10/25/galois-evariste-1811-10-25
        @throws Exception if the slug is incoherent.
    **/
    public static function slug2dir(string $slug): string {
        $p = '/(.*?)\-(\d+)\-(\d{2})\-(\d{2})/';
        preg_match($p, $slug, $m);
        if(count($m) != 5){
            throw new \Exception("Invalid slug: " . $slug);
        }
        $path = [
            $m[2],
            $m[3],
            $m[4],
            $slug,
        ];
        return implode(DS, $path);
    }
    
    
    // *********************** Management of data/wiki/manage/actions.csv ***********************
    /**
        Computes actions from file actions.csv
    **/
    public static function computeActions() {
        $file = self::rootDir() . DS . 'manage' . DS . 'actions.csv';
        if(!is_file($file)){
            throw new \Exception("File does not exist: $file");
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $res = [];
        foreach($lines as $line){
            $fields = explode(';', $line);
            $res[] = [
                'what' => $fields[0],
                'action' => $fields[1],
                'slug' => $fields[2],
            ];
        }
        return $res;
    }
    
    /**
        Adds a line in file actions.csv
        @param  $ation Associative array containing 3 fields: what, action, slug
    **/
    public static function addAction($action) {
        
    }
    
    /**
        Executes an action
        @param  $ation Associative array containing 3 fields: what, action, slug
    **/
    public static function executeAction($action) {
        
    }
    
    
    
} // end class
