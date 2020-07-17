<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;
use tiglib\strings\slugify;
//use tiglib\arrays\cleanEmptyKeys;

class Person{
    
    public $data = [];
    
    // *********************** new *******************************
    
    /**
        Returns an object of type Person from its uid.
        @param $uid     String like person/1876/05/29/parmentier-andre
    **/
    public static function new($uid): Person {
        $p = new Person();
        $p->data['uid'] = $uid;
        $p->load();
        return $p;
    }
    
    /**
        Returns an empty object of type Person.
        Initialized with Person.yml
    **/
    public static function newEmpty(): Person {
        $p = new Person();
        $p->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Person.yml'));
        return $p;
    }
    
    // ************************ id ******************************
    /**
        Unique id in g5 database.
        Corresponds to the relative path to the directory where the person is stored in 7-full/
        ex : person/1811/10/25/galois-evariste
    **/
    public function uid() : string {
        if($this->data['uid']){
            return $this->data['uid'];
        }
        // Build the uid from slug
        $slug = $this->slug(true);                        
        $date = $this->birthday();
        if($date == ''){
            return implode(DB5::SEP, ['tmp', 'lost', 'person', $slug]);
        }
        [$y, $m, $d] = explode('-', $date);
        return implode(DB5::SEP, ['person', $y, $m, $d, $slug]);
    }
    
    /**
        A string which can be used in a url
        ex:
        if $short = false : galois-evariste-1811-10-25
        if $short = true  : galois-evariste
        @pre    The person must have a valid uid
    **/
    public function slug($short=false): string {
        if($this->data['uid']){
            // ex of uid : person/1898/05/22/acito-alfredo
            preg_match('#person/(\d{4})/(\d{2})/(\d{2})/(.*)#', $this->uid(), $m);
            if(count($m) != 5){
                throw new Exception("INVALID PERSON UID : " . $this->uid());
            }
            if($short){
                return $m[4];
            }
            return $m[4] . '-' . $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        else if($this->data['name']['family']){
            $name = $this->data['name']['family'] . (isset($this->data['name']['given']) ? ' ' . $this->data['name']['given'] : '');
            if($short){
                // galois-evariste
                return slugify::compute($name);
            }
            if($this->birthday()){
                // galois-evariste-1811-10-25
                return slugify::compute($name . '-' . $this->birthday());
            }
            return slugify::compute($name);
        }
        else{
            throw new Exception("Person->slug() computation impossible - needs either uid or family name");
        }
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
        ex: /path/to/g5data/7-full/person/1811/10/25/galois-evariste-1811-10-25
        @param $full if false, return path relative in 7-full/
    **/
    public function dir($full=true): string {
        $res = $full ? DB5::$DIR . DB5::SEP : '';
        $res .= $this->uid();
        return str_replace(DB5::SEP, DS, $res);
    }
    
    /** 
        Absolute or relative path to the main yaml file reprensenting the person
        ex: /path/to/g5data/7-full/person/1811/10/25/galois-evariste/galois-evariste-1811-10-25.yml
        @param $full see {@link path()}
    **/
    public function file($full=true): string {
        return $this->dir($full) . DS . $this->slug() . '.yml';
    }
    
    /** Read from disk **/
    public function load(){
        $this->data = yaml_parse(file_get_contents($this->file()));
    }
    
    /** Write to disk **/
    public function save(){
        if(!is_dir($this->dir())){
            mkdir($this->dir(), 0755, true);
        }
        $this->data['uid'] = $this->uid();
        $this->data['slug'] = $this->slug();
        //$this->data['file'] = $this->file();
//        $this->clean();
        file_put_contents($this->file(), yaml_emit($this->data));
        // @todo log diwk write
        // echo "write on disk " . $this->file() . "\n";
    }
    
    // *********************** get *******************************
    public function simple($format=[]){
        // remove ?
    }
    
    // *********************** update fields *******************************
    
    public function clean(){
        cleanEmptyKeys::compute($this->data);
    }
    
    public function update($replace){
        // calling addHistory() before calling update() is left to client code
        // Decision could be made to call addHistory() here to impose to trace all modifications
        //$this->addHistory();
        $this->data = array_replace_recursive($this->data, $replace);
//echo "\n<pre>"; print_r($this); echo "</pre>\n"; exit;
        if($this->data['uid'] == ''){
            $this->data['uid'] = $this->uid();
        }
    }
    
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
    
    
}// end class
