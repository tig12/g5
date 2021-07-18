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


class Source {
    
    /** Structure described by src/model/Source.yml **/
    public $data = [];                                              
    
    public function __construct(){
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Source.yml'));
    }
    
    // *********************** SourceI *******************************
    /** 
        Computes a Source object from its yaml file.
    **/
    public static function getSource($yamlFile): Source {
        $s = new Source();
        $yaml = @yaml_parse(file_get_contents($yamlFile));
        if($yaml === false){
            throw new \Exception("Unable to read source definition file $yamlFile");
        }
        // Allow yaml file containing a field author instead of authors
        if(isset($yaml['author'])){
            $yaml['authors'] = [$yaml['author']];
            unset($yaml['author']);
        }
        $s->data = array_replace_recursive($s->data, $yaml);
        return $s;
    }
    
    // *********************** Get *******************************
    
    /** Creates an object of type Source from storage, using its id. **/
    public static function get($id): ?Source {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from source where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $res['parents'] = json_decode($res['parents'], true);
        $s = new Source();
        $s->data = $res;
        return $s;
    }
    
    /** Creates an object of type Source from storage, using its slug. **/
    public static function getBySlug($slug): ?Source {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from source where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $res['parents'] = json_decode($res['parents'], true);
        $s = new Source();
        $s->data = $res;
        return $s;
    }
    
    // *********************** CRUD *******************************
    
    /**
        Inserts a source in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
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
            ) values(?,?,?,?,?,?,?,?) returning id");
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
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }
    
    /**
        Updates a source in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public function update() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update source set
            slug=?,
            name=?,
            type=?,
            authors=?,
            edition=?,
            isbn=?,
            description=?,
            parents=?
            where id=?");
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['type'],
            json_encode($this->data['authors']),
            $this->data['edition'],
            $this->data['isbn'],
            $this->data['description'],
            json_encode($this->data['parents']),
            $this->data['id'],
        ]);
    }
    
} // end class