<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use tiglib\strings\slugify;

class Person {
    
    public $data = [];
    
    public function __construct(){
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Person.yml'));
    }
    
    // *********************** Storage *******************************
    
    /** Creates an object of type Person from storage, using its id. **/
    public static function get($id): Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Person();
        }
        $res['sources'] = json_decode($res['sources'], true);
        $res['ids_in_sources'] = json_decode($res['ids_in_sources'], true);
        $res['name'] = json_decode($res['name'], true);
        $res['occus'] = json_decode($res['occus'], true);
        $res['birth'] = json_decode($res['birth'], true);
        $res['death'] = json_decode($res['death'], true);
        $res['raw'] = json_decode($res['raw'], true);
        $res['history'] = json_decode($res['history'], true);
        $p = new Person();
        $p->data = $res;
        return $p;
    }
    
    /** Creates an object of type Person from storage, using its slug. **/
    public static function getBySlug($slug): Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Person();
        }
        $res['sources'] = json_decode($res['sources'], true);
        $res['ids_in_sources'] = json_decode($res['ids_in_sources'], true);
        $res['name'] = json_decode($res['name'], true);
        $res['occus'] = json_decode($res['occus'], true);
        $res['birth'] = json_decode($res['birth'], true);
        $res['death'] = json_decode($res['death'], true);
        $res['raw'] = json_decode($res['raw'], true);
        $res['history'] = json_decode($res['history'], true);
        $p = new Person();
        $p->data = $res;
        return $p;
    }
    
    /**
        Inserts a new person in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public static function insert(Person $p): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into person(
            slug,
            sources,
            ids_in_sources,
            trust,
            sex,
            name,
            occus,
            birth,
            death,
            raw,
            history
            )values(?,?,?,?,?,?,?,?,?,?,?) returning id");
        $stmt->execute([
            $p->data['slug'],
            json_encode($p->data['sources']),
            json_encode($p->data['ids_in_sources']),
            $p->data['trust'],
            $p->data['sex'],
            json_encode($p->data['name']),
            json_encode($p->data['occus']),
            json_encode($p->data['birth']),
            json_encode($p->data['death']),
            json_encode($p->data['raw']),
            json_encode($p->data['history']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }

    /**
        Updates a person in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public static function update(Person $p) {
        $stmt = $dblink->prepare("update person set
            slug=?,
            sources=?,
            ids_in_sources=?,
            trust=?,
            sex=?,
            name=?,
            occus=?,
            birth=?,
            death=?,
            raw=?,
            history=?      
            where id=?");
        $stmt->execute([
            $p->data['slug'],
            json_encode($p->data['sources']),
            json_encode($p->data['ids_in_sources']),
            $p->data['trust'],
            $p->data['sex'],
            json_encode($p->data['name']),
            json_encode($p->data['occus']),
            json_encode($p->data['birth']),
            json_encode($p->data['death']),
            json_encode($p->data['raw']),
            json_encode($p->data['history']),
            $p->data['id'],
        ]);
    }
    
    // *********************** Fields *******************************
    
    /**
        Computes the slug of a person.
        ex :
            - galois-evariste-1811-10-25 for a person with a known birth time.
            - galois-evariste for a person without a known birth time.
        throws \Exception if the person id computation impossible (the person has no family name).
    **/
    public function computeSlug() {
        if(!$this->data['name']['family']){
            throw new Exception("Person->computeSlug() impossible - needs family name");
        }
        $name = $this->data['name']['family'] . (isset($this->data['name']['given']) ? ' ' . $this->data['name']['given'] : '');
        if($this->birthday()){
            // galois-evariste-1811-10-25
            $this->data['slug'] = slugify::compute($name . '-' . $this->birthday());
        }
        else{
            // galois-evariste
            $this->data['slug'] = slugify::compute($name);
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
    
    // *********************** update fields *******************************
    
    public function updateFields($replace){
        // calling addHistory() before calling update() is left to client code
        // Decision could be made to call addHistory() here to impose to trace all modifications
        $this->data = array_replace_recursive($this->data, $replace);
    }
    
    public function addIdInSource($source, $id){
        $this->data['ids_in_sources'][$source] = $id;
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
