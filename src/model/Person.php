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
    
    /**
        Converts a row of table person to an object of type Person
        @param $row Assoc array, row of table person
    **/
    public static function row2person($row){
        $row['to-check'] = $row['to_check'];
        unset($row['to_check']);
        $row['sources'] = json_decode($row['sources'], true);
        $row['ids-in-sources'] = json_decode($row['ids_in_sources'], true);
        unset($row['ids_in_sources']);
        $row['trust-details'] = json_decode($row['trust_details'], true);
        unset($row['trust_details']);
        $row['name'] = json_decode($row['name'], true);                             
        $row['occus'] = json_decode($row['occus'], true);
        $row['birth'] = json_decode($row['birth'], true);
        $row['death'] = json_decode($row['death'], true);
        $row['raw'] = json_decode($row['raw'], true);
        $row['history'] = json_decode($row['history'], true);
        $row['notes'] = json_decode($row['notes'], true);
        $p = new Person();
        $p->data = $row;
        return $p;
    }
    
    // *********************** Get *******************************
    
    /** Creates an object of type Person from storage, using its id. **/
    public static function get($id): ?Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    /** Creates an object of type Person from storage, using its slug. **/
    public static function getBySlug($slug): ?Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    /**
        Creates an object of type Person from storage,
        using its id for a given source.
        Ex : to get a person whose id in source A1 is 254, call
        getBySourceId('A1', '254')
        @param  $source     Slug of the source
        @param  $idInSource Local id of the person within this source 
    **/
    public static function getBySourceId($source, $idInSource): ?Person {
        $dblink = DB5::getDbLink();
        //$stmt = $dblink->prepare("select * from person where ids_in_sources @> '{\"?\": \"?\"}'");
        //$stmt->execute([$source, $idInSource]);
        $stmt = $dblink->prepare("select * from person where ids_in_sources @> '{\"$source\": \"$idInSource\"}'");
        $stmt->execute([]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    /**
        Returns an array of Persons with given occupation codes.
        @param $occus Array of occupation codes
    **/
    public static function getByOccu($occus){     
        $dblink = DB5::getDbLink();
        $query = "select * from person where ";
        $parts = [];
        foreach($occus as $occu){
            $parts[] .= "'[\"$occu\"]' <@ occus";
        }
        $query .= implode(' or ', $parts);
        $res = [];
        foreach($dblink->query($query) as $row){
            $res[] = self::row2person($row);
        }
        return $res;
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
            to_check,
            sources,
            ids_in_sources,
            trust,
            trust_details,
            sex,
            name,
            occus,
            birth,
            death,
            raw,
            history,
            notes
            )values(?,?,?,?,?,?,?,?,?,?,?,?,?,?) returning id");
        $stmt->execute([
            $this->data['slug'],
            $this->data['to-check'],
            json_encode($this->data['sources']),
            json_encode($this->data['ids-in-sources']),
            $this->data['trust'],
            json_encode($this->data['trust-details']),
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['raw']),
            json_encode($this->data['history']),
            json_encode($this->data['notes']),
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
            to_check=?,
            sources=?,
            ids_in_sources=?,
            trust=?,
            trust_details=?,
            sex=?,
            name=?,
            occus=?,
            birth=?,
            death=?,
            raw=?,
            history=?,
            notes=?
            where id=?
            ");
        $stmt->execute([
            $this->data['slug'],
            $this->data['to-check'],
            json_encode($this->data['sources']),
            json_encode($this->data['ids-in-sources']),
            $this->data['trust'],
            json_encode($this->data['trust-details']),
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['raw']),
            json_encode($this->data['history']),
            json_encode($this->data['notes']),
            $this->data['id'],
        ]);
    }
    
    // *********************** Fields *******************************
    
    /**
        @throws \Exception if the person id computation impossible (the person has no family name).
    **/
    public function getIdFromSlug($slug) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select id from person where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            throw new \Exception("Trying to get a person with unexisting slug : $slug");
        }
        return $res['id'];
    }
    
    /**
        Computes the slug of a person.
        ex :
            - galois-evariste-1811-10-25 for a person with a known birth time.
            - galois-evariste for a person without a known birth time.
        @throws \Exception if the person id computation impossible (the person has no family name).
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
        Computes the birth day from date or date-ut
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
    
    /** 
        Replaces $this->data with fields present in $replace.
        Fields of $this->data not present in $replace are not modified.
        @param $replace Assoc. array with the same structure as $this->data
    **/
    public function updateFields($replace){
        $this->data = array_replace_recursive($this->data, $replace);
    }
    
    public function addIdInSource($source, $id){
        $this->data['ids-in-sources'][$source] = $id;
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
    
    public function addNote($note){
        $this->data['notes'][] = $note;
    }
    
    public function addRaw($source, $data){
        $this->data['raw'][$source] = $data;
    }
    
    
}// end class
