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
    
    /** Actions for data/wiki/manage/actions.csv **/
    const   ACTION_ADD    = 'add';
    const   ACTION_UPDATE = 'upd';
    const   ACTION_DELETE = 'del';
    
    /** Separator used in actions.csv **/
    const ACTION_SEP = ';';
    
    /**
        Hack to put something in field "raw" of history.
        Because go web application needs something (empty map is considered as empty array).        
    **/
    const BASE_URL = 'https://github.com/tig12/ogdb-wiki/tree/main/person';
    
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
        Returns the path to data/wiki/manage/actions.csv
    **/
    public static function getActionFilePath() {
        return self::rootDir() . DS . 'manage' . DS . 'actions.csv';
    }
    
    /**
        Computes actions from file actions.csv
    **/
    public static function computeAllActions() {
        $file = self::getActionFilePath();
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
        @param  $action Associative array containing 3 fields: what, action, slug
                    what   Possible values: 'bc'
                    action Possible values: 'add', 'upd', 'del'.
                    slug   Slug of the thing to add
        @throws Exception if invalid parameter or if file actions.csv does not exist.
    **/
    public static function addAction(array $action): void {
        $msg = self::check_what($action['what']);
        if($msg != ''){
            throw new \Exception($msg);
        }
        $msg = self::check_action($action['action']);
        if($msg != ''){
            throw new \Exception($msg);
        }
        $file = self::getActionFilePath();
        if(!is_file($file)){
            throw new \Exception("Unexisting file: $file");
        }
        $newContent = implode(self::ACTION_SEP, $action) . "\n";
        file_put_contents($file, $newContent, FILE_APPEND);
    }
    
    /**
        Auxiliary of methods concerning actions.csv
        @return Error message or empty string if valid.
    **/
    public static function check_what(string $what): string {
        if(!in_array($what, ['bc'])){
            return "Invalid parameter what: '$what'";
        }
        return '';
    }
    
    /**
        Auxiliary of methods concerning actions.csv
        @return Error message or empty string if valid.
    **/
    public static function check_action(string $action): string {
        if(!in_array($action, [self::ACTION_ADD, self::ACTION_UPDATE, self::ACTION_DELETE])){
            return "Invalid parameter action: '$action'";
        }
        return '';
    }
    
} // end class
