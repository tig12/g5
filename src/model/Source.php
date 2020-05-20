<?php
/******************************************************************************

    @license    GPL
    @history    2020-04-30 16:59:43+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;
use g5\model\G5DB;
use tiglib\strings\slugify;
use tiglib\filesystem\globRecursive;


class Source{
    
    public $data = [];
    
    /** 
        Relative path within G5DB::$DIR_INDEX to the file maintaining associations between source ids and uids
    **/
    const INDEX_ID_UID = 'source/id-uid.txt';
    
    // *********************** new *******************************
    
    /** Returns an object of type Source. **/
    public static function new($uid): Source {
        $s = new Source();
        $s->data['uid'] = $uid;
        $s->load();
        return $s;
    }
    
    /**
        Returns an empty object of type Source.
        Initialized with Source.yml
    **/
    public static function newEmpty(): Source {
        $s = new Source();
        $s->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Source.yml'));
        return $s;
    }
    
    // ************************ uid ******************************
    /**
        Returns the uid, unique id in g5 database.
        Corresponds to the relative path where it is stored in 7-full/
        ex : source/web/cura/A1 corresponds to source/web/cura/A1.yml
    **/
    public function uid() : string {
        return $this->data['uid'];
    }
    
    // *********************** file system *******************************
    
    /** 
        Absolute or relative path to the main yaml file contianing source informations.
        ex: /path/to/g5data/7-full/source/cura/A1.yml
        @param $full if false, return path relative in 7-full/
    **/
    public function file($full=true): string {
        $res = $full ? G5DB::$DIR . G5DB::SEP : '';
        $res .= str_replace(G5DB::SEP, DS, $this->uid()) . '.yml';
        return $res;
    }
    
    public function load(){
        $this->data = yaml_parse(file_get_contents($this->file()));
    }
    
    public function save(){
        $file = $this->file();
        $dir = dirname($file);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($file, yaml_emit($this->data)); // echo "___ file_put_contents $file\n";
    }
    
    // *********************** fields *******************************
    
    // *********************** index *******************************
    // ******************************************************
    /**
        Rewrites index/source/id-uid.txt
        
    **/
    public static function reindexIdUid(){
        $files = globRecursive::execute(G5DB::$DIR_SOURCE . DS . '*.yml');
        $lines = [];
        foreach($files as $file){
            if($file == 'Source'){
                continue; // empty source to copy to create a new source
            }
            // yaml parse issues a warning if a yaml file is empty
            $data = yaml_parse(file_get_contents($file));
            if(isset($data['id']) && isset($data['uid'])){
                $lines[$data['id']] = $data['uid'];
            }
        }
        $res = '';
        foreach($lines as $k => $v){
            $res .= "$k $v\n";
        }
        file_put_contents(G5DB::$DIR_INDEX . DS . self::INDEX_ID_UID, $res);
    }
    
} // end class