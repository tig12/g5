<?php
/******************************************************************************

    @license    GPL
    @history    2020-04-30 16:59:43+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                                                      
use tiglib\strings\slugify;

class Source{
    
    public $data = [];
    
    // *********************** new *******************************
    
    /** Returns an object of type Source. **/
    public static function new($uid): Source {
        $p = new Source();
        $p->data['uid'] = $uid;
        $p->load();
        return $p;
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
        ex : source/cura/A1
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
        return str_replace(G5DB::SEP, DS, $this->uid()) . '.yml';
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
    
} // end class