<?php
/******************************************************************************

    @license    GPL
    @history    2020-04-30 16:59:43+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                                                      
use tiglib\strings\slugify;

class Source{
    
    public $uid = '';
    
    public $data = [];
    
    // *********************** new *******************************
    
    /** Returns an object of type Source. **/
    public static function new($uid): Source {
        $p = new Source();
        $p->uid = $uid;
        $p->load();
        return $p;
    }
    
    /**
        Returns an empty object of type Source.
        Initialized with Source.yml
    **/
    public static function newEmpty(): Source {
        $p = new Source();
        $p->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Source.yml'));
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
        return implode(G5DB::SEP, ['sources', $slug]);
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
    
} // end class