<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                                                      
use tiglib\strings\slugify;

class Person{
    
    // instance fields
    public $data = [];
    
    // ******************************************************
    /**
        @param $
    **/
    public static function new(&$data=[], &$params=[]){
        $p = new Person();
        if(!isset($data['name']) && !isset($data['birth']['date'])){
            // empty person
            $p->data = yaml_parse(file_get_contents(dirname(__FILE__) . DS . 'Person.yml'));
        }
        return $p;
        
        // todo check that syntax of $data is conform to person
        //$file = self::filename($data);
    }
    
    
    // ******************************************************
    public function addId($source, $id){
        $this->data['ids'][$source] = $id;
    }
    
    // ******************************************************
    public function addSource($source){
        if(!in_array($source, $this->data['sources'])){
            $this->data['sources'][] = $source;
        }
    }
    
    // ******************************************************
    public function addOccu($occu){
        if(!in_array($occu, $this->data['occus'])){
            $this->data['occus'][] = $occu;
        }
    }
    
    // ******************************************************
    public function addHistory($source, $data){
        $this->data['history'][] = [
            'source' => $source,
            'values' => $data,
        ];
    }
    
    // ******************************************************
    /**
    **/
    public function update($new){
        $this->data = array_replace_recursive($this->data, $new);
    }
    
    // ******************************************************
    /**
    **/
    public function save(){
        $date = '';
        if(isset($this->data['birth']['date'])){
            $date = $this->data['birth']['date'];
        }
        else if(isset($this->data['birth']['date-ut'])){
            $date = $this->data['birth']['date-ut'];
        }
        // @todo check that name is present 
        $dir = self::dirname($date);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        $filename = self::filename($this->data['name'], $date);
        $file = $dir . DS . $filename;
        $yaml = yaml_emit($this->data);
        file_put_contents($file, $yaml);
    }
    
    
    // ******************************************************
    // ***************** static functions *******************
    // ******************************************************
    
    // ******************************************************
    /**
        Returns the path to a file where a person is stored in 5-tmp/full
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The full path or false if $birthdate is not formatted correctly.
    **/
    public static function filename($name, $date){
        $slug = self::slug($name, $date);
        return $slug . '.yml';
    }
    
    // ******************************************************
    /**
        Returns the path to sub-directory of 5-tmp/full corresponding too $birthdate
        @param $birthdate a date in ISO 8601 format, starting by YYYY-MM-DD.
                Can contain or not birth time and time zone information.
        @return The path or false if $birthdate is not formatted correctly.
    **/
    public static function dirname($birthdate){
        if(preg_match(Full::PDATE, $birthdate) != 1){
            return Full::$DIR . DS . 'lost'; 
        }
        $date = substr($birthdate, 0, 10);
        [$y, $m, $d] = explode('-', $date);
        return implode(DS, [Full::$DIR, 'persons', $y, $m, $d]);
    }
    
    
    // ******************************************************
    /**
        
        @param $
    **/
    public static function slug($name, $date): string {
        $slug = '';
        $bd = substr($date, 0, 10);
        return slugify::compute("$name-$bd");
    }
    public static function slugOLD($name, $fname, $gname, $birthdate): string {
        $slug = '';
        $bd = substr($birthdate, 0, 10);
        if($fname && $gname){
            return slugify::compute("$fname-$gname-$bd");
        }
        if($name){
            return slugify::compute("$name-$bd");
        }
        if($fname){
            return slugify::compute("$fname-$bd");
        }
        if($gname){
            return slugify::compute("$fname-$bd");
        }
        else{
            throw new \Exception("CANNOT COMPUTE SLUG :\n    name = $name\n    fname = $fname\n    gname = $gname\n    birthdate = $birthdate");
        }
    }
    
    
    
}// end class
