<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                                                      
use tiglib\strings\slugify;

class Person{
    
    public $uid = '';
    
    public $data = [];
    
    // ******************************************************
    /**
        Returns a new Person object.
    **/
    public static function new(&$data=[], &$params=[]){
        $p = new Person();
        if(!isset($data['name']) && !isset($data['birth']['date'])){
            // empty person
            $p->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Person.yml'));
        }
        else{
            // read from yaml file
        }
        return $p;
    }
    
    // ******************************************************
    /**
        Unique id in g5 database.
        Corresponds to the path where it is stored in 7-full/
        ex : persons/1905/02/05/lantier-pierre
             lost/fistule-hibere
    **/
    public function uid(){
        [$y, $m, $d] = explode('-', $this->dateClean());
        return implode(Full::SEP, ['persons', $y, $m, $d, $this->slug()]);
    }
    
    public function slug(): string {
        return slugify::compute($this->data['name']);
        //$bd = substr($this->dateClean(), 0, 10);
        //return slugify::compute("$name-$bd");
    }
    
    /** Name of the file where a person is stored in 7-full **/
    public function filename(): string {
        return $this->slug() . '.yml';
    }
    
    /** Returns the path to sub-directory of 7-full/ **/
    public function dirname($full=true): string {
        if(preg_match(Full::PDATE, $this->dateClean()) != 1){
            return $full ? Full::$DIR . Full::SEP . 'lost' : 'lost';
        }
        return $full ? Full::$DIR . Full::SEP . $this->uid() : $this->uid();
    }
    
    // ******************************************************
    /** Date trimmed and (en théorie) present **/
    private function dateClean(){
        if(isset($this->data['birth']['date'])){
            $date = $this->data['birth']['date'];
        }
        else if(isset($this->data['birth']['date-ut'])){
            $date = $this->data['birth']['date-ut']; // for cura A
        }
        return substr($date, 0, 10);
    }
    
    // ******************************************************
    public function save(){
        $dir = str_replace(Full::SEP, DS, self::dirname());
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        $filename = $this->filename();
        $file = $dir . DS . $filename;
        $yaml = yaml_emit($this->data);
        file_put_contents($file, $yaml);
    }
    
    // ******************************************************
    
    public function addId($source, $id){
        $this->data['ids'][$source] = $id;
    }
    
    public function addSource($source){
        if(!in_array($source, $this->data['sources'])){
            $this->data['sources'][] = $source;
        }
    }
    
    public function addOccu($occu){
        if(!in_array($occu, $this->data['occus'])){
            $this->data['occus'][] = $occu;
        }
    }
    
    public function addHistory($source, $data){
        $this->data['history'][] = [
            'source' => $source,
            'values' => $data,
        ];
    }
    
    public function update($replace){
        $this->data = array_replace_recursive($this->data, $replace);
    }
    
    
    /* 
    public function slugOLD($name, $fname, $gname, $birthdate): string {
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
    */
}// end class
