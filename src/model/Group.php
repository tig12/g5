<?php
/******************************************************************************
    Regular array containing person paths (strings)
        ex of persons/1864/12/16/machin-pierre
    
    @license    GPL
    @history    2019-12-27 23:20:16+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                           

class Group{
    
    public $uid;
    
    /** Array of strings - contains data uids **/
    public $data = [];
    
    // ******************************************************
    /**
        @param $uid     String like groups/datasets/cura/A1
    **/
    public static function new($uid){
        $g = new Group();
        $g->uid = $uid;
        return $g;
    }
    
    public function uid(){
        return $this->uid;
    }
    
    public function slug(): string {
        $tmp = explode(Full::SEP, $this->uid);
        return $tmp[count($tmp)-1];
    }
    
    /** Name of the file where a person is stored in 7-full **/
    public function filename(): string {
        return $this->slug() . '.txt';
    }
    
    /**
        Returns the path to sub-directory of 7-full/
    **/
    public function dirname($full=true): string {
        $tmp = explode(Full::SEP, $this->uid);
        array_pop($tmp);
        $dir = implode(Full::SEP, $tmp);
        return $full ? Full::$DIR . Full::SEP . $dir : $dir;
    }

    // ******************************************************
    public function save(){
        $dir = str_replace(Full::SEP, DS, self::dirname());
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        $filename = $this->filename();
        $file = $dir . DS . $filename;
        $dump = '';
        sort($this->data);
        foreach($this->data as $elt){
            $dump .= $elt . "\n";
        }
        file_put_contents($file, $dump);
    }
    
    // ******************************************************
    public function add($entry){
        $this->data[] = $entry;
    }
    
    
}// end class
