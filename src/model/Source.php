<?php
/******************************************************************************
    
    An object of type Source represents a source in g5 db.
    This class also contains generic methods for source management.
    
    @license    GPL
    @history    2020-04-30 16:59:43+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;
use g5\model\DB5;
use tiglib\strings\slugify;
use tiglib\filesystem\globRecursive;


class Source{
    
    public $data = [];
    
    /** 
        Relative path within DB5::$DIR_INDEX to the file maintaining associations between source ids and uids
    **/
    const INDEX_ID_UID = 'source/id-uid.txt';
    
    // *********************** new *******************************
    
    /**
        Returns an object of type Source from its uid.
        The source is initialized using its yaml file (see load()).
        @param $uid     String like source/web/cura/A1
        @throws Exception if $uid does not correspond to a yaml file.
    **/
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
        $res = $full ? DB5::$DIR . DB5::SEP : '';
        $res .= str_replace(DB5::SEP, DS, $this->uid()) . '.yml';
        return $res;
    }
    
    /** 
        Fills $this->data from this source's yaml file
        @throws Exception if yaml file corresponding to source's uid.
    **/
    public function load(){
        if(!is_file($this->file())){
            throw new \Exception("File " . $this->file() . " does not exist");
        }
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
    
    /**
        Rewrites index/source/id-uid.txt
        @return Report
    **/
    public static function reindexIdUid(): string{
        $files = globRecursive::execute(DB5::$DIR_SOURCE . DS . '*.yml');
        $lines = [];
        foreach($files as $file){
            if($file == DB5::$DIR_SOURCE . DS . 'Source.yml'){
                // Source.yml : empty source to copy to create a new source
                continue;
            }
            // yaml parse issues a warning if a yaml file is empty
            $data = yaml_parse(file_get_contents($file));
            if(isset($data['id']) && isset($data['uid'])){
                $lines[$data['id']] = $data['uid'];
            }
        }
        $res = '';
        $n = 0;
        foreach($lines as $k => $v){
            $res .= "$k $v\n";
            $n++;
        }
        $outfile = DB5::$DIR_INDEX . DS . self::INDEX_ID_UID;
        file_put_contents($outfile, $res);
        return "Updated $outfile (wrote $n lines)\n";
    }
    
} // end class