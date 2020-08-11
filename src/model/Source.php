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


class Source {
    
    /** Structure described by src/model/Source.yml **/
    public $data = [];
    
    public function __construct(){
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Source.yml'));
    }
    
    // *********************** Storage *******************************
    
    /** Creates an object of type Source from storage, using its id. **/
    public static function get($id): Source {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from source where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Source();
        }
        $res['source'] = json_decode($res['source'], true);
        $s = new Source();
        $s->data = $res;
        return $s;
    }
    
    /** Creates an object of type Source from storage, using its slug. **/
    public static function getBySlug($slug): Source {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from source where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Source();
        }
        $res['source'] = json_decode($res['source'], true);
        $s = new Source();
        $s->data = $res;
        return $s;
    }
    
    /**
        Inserts a new source in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public static function insert(Source $s): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into source(slug,name,description,source) values(?,?,?,?) returning id");
        $stmt->execute([
            $s->data['slug'],
            $s->data['name'],
            $s->data['description'],
            json_encode($s->data['source']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }
    
    /**
        Updates a source in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public static function update(Source $s) {
        $stmt = $dblink->prepare("update source set slug=?,name=?,description=?,source=? where id=?");
        $stmt->execute([
            $s->data['slug'],
            $s->data['name'],
            $s->data['description'],
            json_encode($s->data['source']),
            $s->data['id'],
        ]);
    }
    
    // *********************** Fields *******************************
    
} // end class