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
    
    // *********************** new *******************************
    
    /** Returns an object of type Person. **/
    public static function new($uid): Person {
        $p = new Person();
        $p->uid = $uid;
        $p->load();
        return $p;
    }
    
    /** Returns an empty object of type Person. **/
    public static function newEmpty(): Person {
        $p = new Person();
        // initialize data from yaml template
        $p->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Person.yml'));
        return $p;
    }
    
    // ************************ id ******************************
    /**
        Unique id in g5 database.
        Corresponds to the relative path where it is stored in 7-full/
        ex : persons/1811/10/25/galois-evariste-1811-10-25
    **/
    public function uid() : string {
        if($this->uid != ''){
            return $this->uid;
        }
        $slug = $this->slug($short=true);                        
        $date = $this->birthday();
        if($date == ''){
            return implode(G5DB::SEP, ['lost', 'persons', $slug]);
        }
        [$y, $m, $d] = explode('-', $date);
        return implode(G5DB::SEP, ['persons', $y, $m, $d, $slug]);
    }
    
    /**
        A string which can be used in an url
        ex:
        if $short = false : galois-evariste-1811-10-25
        if $short = true  : galois-evariste
    **/
    public function slug($short=false): string {
        if($short){            
            // galois-evariste
            return slugify::compute($this->data['name']['family'] . '-' . $this->data['name']['given']);
        }
        // galois-evariste-1811-10-25
        return slugify::compute($this->data['name']['family'] . '-' . $this->data['name']['given'] . '-' . $this->birthday());
    }
    
    /**
        @return YYYY-MM-DD or ''
    **/
    private function birthday(): string {
        if(isset($this->data['birth']['date'])){
            return substr($this->data['birth']['date'], 0, 10);
        }
        else if(isset($this->data['birth']['date-ut'])){
            // for cura A
            return substr($this->data['birth']['date-ut'], 0, 10);
        }
        return '';
    }
    
    // *********************** file system *******************************
    
    /** 
        Absolute or relative path to the directory containing person's data
        ex: /path/to/g5data/7-full/persons/1811/10/25/galois-evariste-1811-10-25
        @param $full if false, return path relative in 7-full/
    **/
    public function dir($full=true): string {
        $res = $full ? G5DB::$DIR . G5DB::SEP : '';
        $res .= $this->uid();
        return str_replace(G5DB::SEP, DS, $res);
    }
    
    /** 
        Absolute or relative path to the main yaml file reprensenting the person
        ex: /path/to/g5data/7-full/persons/1811/10/25/galois-evariste/galois-evariste-1811-10-25.yml
        @param $full see {@link path()}
    **/
    public function file($full=true): string {
        return $this->dir($full) . DS . $this->slug() . '.yml';
    }
    
    public function load(){
        $this->data = yaml_parse(file_get_contents($this->file()));
    }
    
    public function save(){
        if(!is_dir($this->dir())){
            mkdir($this->dir(), 0755, true);
        }
        file_put_contents($this->file(), yaml_emit($this->data)); // echo "___ file_put_contents $path\n";
    }
    
    // *********************** fields *******************************
    
    public function addId($source, $id){
        $this->data['ids'][$source] = $id;
    }
    
    public function addOccu($occu){
        if(!in_array($occu, $this->data['occus'])){
            $this->data['occus'][] = $occu;
        }
    }
    
    public function addSource($source){
        if(!in_array($source, $this->data['sources'])){
            $this->data['sources'][] = $source;
        }
    }
    
    public function addHistory($command, $source, $data){
        $this->data['history'][] = [
            'date'      => date('c'),
            'command'   => $command,
            'source'    => $source,
            'values'    => $data,
        ];                                       
    }
    
    public function addRaw($source, $data){
        $this->data['raw'][$source] = $data;
    }
    
    public function update($replace){
        //$this->addHistory();
        $this->data = array_replace_recursive($this->data, $replace);
    }
    
}// end class
