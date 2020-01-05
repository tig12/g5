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
        ex : persons/1905/02/05/lantier-pierre
             lost/fistule-hibere
    **/
    public function uid() : string {
        if($this->uid != ''){
            return $this->uid;
        }
        $slug = $this->slug();
        $date = $this->birthday();
        if($date == ''){
            return implode(Full::SEP, ['persons', 'lost', $slug]);
        }
        [$y, $m, $d] = explode('-', $date);
        return implode(Full::SEP, ['persons', $y, $m, $d, $slug]);
    }
    
    public function slug(): string {
        return slugify::compute($this->data['name']);
        //return slugify::compute($this->data['name'] . '-' . $this->birthday());
    }
    
    /** @return YYYY-MM-DD or '' **/
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
    
    public function path($full=true): string {
        $res = $full ? Full::$DIR . Full::SEP : '';
        $res .= $this->uid() . '.yml';
        return str_replace(Full::SEP, DS, $res);
    }
    
    public function load(){
        $this->data = yaml_parse(file_get_contents($this->path()));
    }
    
    public function save(){
        $path = $this->path();
        $dir = dirname($path);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, yaml_emit($this->data)); // echo "___ file_put_contents $path\n";
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
