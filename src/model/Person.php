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
    
    // *********************** Get *******************************
    
    /** Creates an object of type Person from storage, using its id. **/
    public static function get($id): Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return new Person();
        }
        return row2person($res);
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
        return row2person($res);
    }
    
    /**
        Converts a row of table person to an object of type Person
        @param $row Assoc array, row of table person
    **/
    public static function row2person($row){
        $row['sources'] = json_decode($row['sources'], true);
        $row['ids_in_sources'] = json_decode($row['ids_in_sources'], true);
        $row['name'] = json_decode($row['name'], true);
        $row['occus'] = json_decode($row['occus'], true);
        $row['birth'] = json_decode($row['birth'], true);
        $row['death'] = json_decode($row['death'], true);
        $row['raw'] = json_decode($row['raw'], true);
        $row['history'] = json_decode($row['history'], true);
        $p = new Person();
        $p->data = $row;
        return $p;
    }
    
    // *********************** CRUD *******************************
    
    /**
        Inserts a new person in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
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
            $this->data['slug'],
            json_encode($this->data['sources']),
            json_encode($this->data['ids_in_sources']),
            $this->data['trust'],
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['raw']),
            json_encode($this->data['history']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res['id'];
    }

    /**
        Updates a person in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public function update() {
        $dblink = DB5::getDbLink();
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
            $this->data['slug'],
            json_encode($this->data['sources']),
            json_encode($this->data['ids_in_sources']),
            $this->data['trust'],
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['raw']),
            json_encode($this->data['history']),
            $this->data['id'],
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
