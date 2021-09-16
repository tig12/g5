<?php
/******************************************************************************
    
    An object of type Source represents a source in g5 db.
    
    @license    GPL
    @history    2020-04-30 16:59:43+02:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\app\Config;
use g5\model\DB5;

Source::init();
class Source {
    
    /**
        Structure of a source object, contained in src/model/Source.yml
    **/
    public $data = [];
    
    /** Root directory containing the yaml source files definitions **/
    public static $DIR;
    
    /** Static initializer, executed once at class loading **/
    public static function init(){
        self::$DIR = Config::$data['dirs']['ROOT'] . DS . Config::$data['dirs']['db'] . DS . 'source';
    }
    
    /** 
        Constructor ; builds an empty source or a source filled from its yaml file definition
        @param  $yamlFile Path of the file containing souce's data.
                Relative to self::$DIR
    **/
    public function __construct($yamlFile = ''){
        
        // Fills an empty source from its structure
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Source.yml'));
        if($yamlFile == ''){
            return; // ok, just build an empty source
        }
        
        // Load source data from data/model/source
        $yamlFile = self::$DIR . DS . $yamlFile;
        $yaml = yaml_parse_file($yamlFile);
        if($yaml === false){
            throw new \Exception("ERROR: Unable to read source definition file $yamlFile");
        }
        // Allow yaml file containing a field 'author' instead of 'authors'
        if(isset($yaml['author'])){
            $yaml['authors'] = [$yaml['author']];
            unset($yaml['author']);
        }
        $this->data = array_replace_recursive($this->data, $yaml);
    }
    
    // *********************** Database operations *******************************
    
    /** Creates an object of type Source from database, using its slug. **/
    public static function getBySlug($slug): ?Source {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from source where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $res['parents'] = json_decode($res['parents'], true);
        $res['authors'] = json_decode($res['authors'], true);
        $s = new Source();
        $s->data = $res;
        return $s;
    }
    
    /**
        Inserts a source in database.
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into source(
                slug,
                name,
                type,
                authors,
                edition,
                isbn,
                description,
                parents
            ) values(?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['type'],
            json_encode($this->data['authors']),
            $this->data['edition'],
            $this->data['isbn'],
            $this->data['description'],
            json_encode($this->data['parents']),
        ]);
    }
    
    /**
        Updates a source in database.
        @throws \Exception if trying to update an unexisting id
    **/
    public function update() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update source set
            name=?,
            type=?,
            authors=?,
            edition=?,
            isbn=?,
            description=?,
            parents=?
            where slug=?");
        $stmt->execute([
            $this->data['name'],
            $this->data['type'],
            json_encode($this->data['authors']),
            $this->data['edition'],
            $this->data['isbn'],
            $this->data['description'],
            json_encode($this->data['parents']),
            $this->data['slug'],
        ]);
    }
    
} // end class