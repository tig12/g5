<?php
/******************************************************************************

    @license    GPL
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use tiglib\strings\slugify;
use tiglib\arrays\flattenAssociative;

class Person {
    
    public $data = [];
    
    public function __construct(){
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Person.yml'));
    }
    
    // ***********************************************************************
    //                                  STATIC
    // ***********************************************************************
    
    /**
        Converts a row of table person to an object of type Person.
        @param $row Assoc array, row of table person.
                    Can be partial (containing only parts of the fields).
    **/
    public static function row2person($row){
        if(isset($row['name'])){
            $row['name'] = json_decode($row['name'], true);                             
        }
        if(isset($row['occus'])){
            $row['occus'] = json_decode($row['occus'], true);
        }
        if(isset($row['birth'])){
            $row['birth'] = json_decode($row['birth'], true);
        }
        if(isset($row['death'])){
            $row['death'] = json_decode($row['death'], true);
        }
        if(isset($row['sources'])){
            $row['sources'] = json_decode($row['sources'], true);
        }
        if(isset($row['ids_in_sources'])){
            $row['ids-in-sources'] = json_decode($row['ids_in_sources'], true);
            unset($row['ids_in_sources']);
        }
        if(isset($row['trust'])){
            $row['trust'] = json_decode($row['trust'], true);
        }
        if(isset($row['acts'])){
            $row['acts'] = json_decode($row['acts'], true);
        }
        if(isset($row['history'])){
            $row['history'] = json_decode($row['history'], true);
        }
        if(isset($row['todo'])){
            $row['todo'] = json_decode($row['todo'], true);
        }
        if(isset($row['notes'])){
            $row['notes'] = json_decode($row['notes'], true);
        }
        $p = new Person();
        $p->data = $row;
        return $p;
    }
    
    // *********************** Get *******************************
    
    /**
        Returns an object of type Person from storage, using its slug,
        or null if doesn't exist.
    **/
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
        Returns an object of type Person from storage,
        using its id for a given source,
        or null if doesn't exist.
        Ex : to get a person whose id in source a1 is 254, call
        getBySourceId('a1', '254')
        @param  $source     Slug of the source
        @param  $idInSource Local id of the person within this source 
    **/
    public static function getBySourceId($sourceSlug, $idInSource): ?Person {
        $dblink = DB5::getDbLink();
        //$stmt = $dblink->prepare("select * from person where ids_in_sources @> '{\"?\": \"?\"}'");
        //$stmt->execute([$sourceSlug, $idInSource]);
        $stmt = $dblink->prepare("select * from person where ids_in_sources @> '{\"$sourceSlug\": \"$idInSource\"}'");
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
    
    /** 
        Static computation of slug.
        Normally done with family and given name, but $name1 and $name2 may contain
        other informations, like fame name.
        @param  $name1 Generally family name
        @param  $name2 Generally given name 
        
        @see    instance method computeSlug()
    **/
    public static function doComputeSlug($name1, $name2, $date): string {
        $name = $name1 . ($name2 != '' ? ' ' . $name2 : '');
        if($date != ''){
            // galois-evariste-1811-10-25
            $slug = slugify::compute($name . '-' . $date);
        }
        else{
            // galois-evariste
            $slug = slugify::compute($name);
        }
        return $slug;
    }
    
    /**
        Computes the different forms of a person name.
        @param  $array_name  Array representing the name, as stored in database. Ex: [
            'usual' => ''
            'given' => Pierre
            'family' => Alard
            'spouse' => ''
            'official' => [
                'given' => ''
                'family' => ''
            ]
            'fame' => [
                'full' => ''
                'given' => ''
                'family' => ''
            ]
            'alter' => []
            'nobl' => ''
        ]
    **/
    public static function computeNames(array $array_name) {
        $res = [];
        if($array_name['usual'] != ''){
            $res[] = $array_name['usual'];
        }
        if($array_name['fame'] != ''){
            $res[] = $array_name['fame'];
        }
        if(!empty($array_name['alter'])){
            foreach($array_name['alter'] as $alt){
                $res[] = $alt;
            }
        }
        if($array_name['given'] != '' && $array_name['family'] != ''){
            $res[] = $array_name['given'] . ' ' . $array_name['family'];
        }
        else if($array_name['family'] != ''){
            $res[] = $array_name['family'];
        }
        return $res;
    }
    
    // ***********************************************************************
    //                                  INSTANCE
    // ***********************************************************************
    
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
        @see    static method doComputeSlug()
    **/
    public function computeSlug() {
        $name1 = $name2 = '';
        if($this->data['name']['family']){
            $name1 = $this->data['name']['family'];
            $name2 = $this->data['name']['given'];
        }
        else if($this->data['name']['fame']['full']){
            $name1 = $this->data['name']['fame']['full'];
        }
        else if($this->data['name']['fame']['family']){
            $name1 = $this->data['name']['fame']['family'];
            $name2 = $this->data['name']['fame']['given'];
        }
        else{
            throw new \Exception(
                "Person->computeSlug() impossible - information missing\n"
                . "Available info = " . print_r($this->data['name'], true) . "\n"
            );
        }
        $this->data['slug'] = self::doComputeSlug($name1, $name2, $this->birthday());
        return;
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
    
    /** 
        Adds or updates a couple (source slug, id) in data['ids-in-sources']
    **/
    public function addIdInSource($sourceSlug, $id){
        $this->data['ids-in-sources'][$sourceSlug] = $id;
    }
    
    /** Adds one single source to field sources **/
    public function addSource($sourceSlug){
        if(!in_array($sourceSlug, $this->data['sources'])){
            $this->data['sources'][] = $sourceSlug;
        }
    }
    
    /**
        Adds one single occupation to field occus.
        Does not check if an added slug represents a child or parent of current occupations.
    **/
    public function addOccu($occuSlug){
        if(!in_array($occuSlug, $this->data['occus'])){
            $this->data['occus'][] = $occuSlug;
        }                              
    }
    
    /**
        Adds an array of occupation slugs to field occus.
        "Cleans" the occupation by removing useless (redundant) slugs.
        For example, 
        - if $occuSlugs contains "dancer", and field occus already contains "artist", then "artist" is removed.
        - if $occuSlugs contains "artist", and field occus already contains "dancer", then "artist" is not added.
        Always remove the parents and keep the children, which are more specific.
    **/
    public function addOccus($occuSlugs){
        $occus = $this->data['occus'];
        foreach($occuSlugs as $occuSlug){
            if(!in_array($occuSlug, $occus)){
                $occus[] = $occuSlug;
            }                              
        }
        $ancestors = Group::getAllAncestors();
        $remove = [];
        foreach($occus as $occu1){
            foreach($occus as $occu2){
                if($occu1 == $occu2){
                    continue;
                }
                if(in_array($occu1, $ancestors[$occu2])){
                    $remove[] = $occu1;
                }
            }
        }
        // note:
        // - array_values() permits to reindex
        // - if array_values() not used, jsonb is not stored as a regular array, but as an associative array.
        $this->data['occus'] = array_values(array_diff($occus, $remove));
    }
    
    public function addHistory($command, $sourceSlug, $newdata, $rawdata){
        $this->data['history'][] = [
            'date'      => date('c'),
            'command'   => $command,
            'source'    => $sourceSlug,
            'new'       => $newdata,
            // flatten necessary because in the go application, a raw entry is typed map[string]string
            'raw'       => flattenAssociative::compute($rawdata),
        ];
    }
    
    /** 
        Adds an array of alternative names.
    **/
    public function addAlternativeNames($newdata){
        foreach($newdata as $alter){
            if(!in_array($alter, $this->data['name']['alter'])){
                $this->data['name']['alter'][] = $alter;
            }
        }
    }
    
    /** 
        Adds an array of act
    **/
    public function addActs($newdata){
        foreach($newdata as $act){
            if(!in_array($act, $this->data['acts'])){
                $this->data['acts'][] = $act;
            }
        }
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
            sex,
            name,
            occus,
            birth,
            death,
            sources,
            ids_in_sources,
            trust,
            acts,
            history,
            todo,
            notes
            )values(?,?,?,?,?,?,?,?,?,?,?,?,?) returning id");
        $stmt->execute([
            $this->data['slug'],
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['sources']),
            json_encode($this->data['ids-in-sources']),
            json_encode($this->data['trust']),
            json_encode($this->data['acts']),
            json_encode($this->data['history']),
            json_encode($this->data['todo']),
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
            sex=?,
            name=?,
            occus=?,
            birth=?,
            death=?,
            sources=?,
            ids_in_sources=?,
            trust=?,
            acts=?,
            history=?,
            todo=?,
            notes=?
            where id=?
            ");
        $stmt->execute([
            $this->data['slug'],
            $this->data['sex'],
            json_encode($this->data['name']),
            json_encode($this->data['occus']),
            json_encode($this->data['birth']),
            json_encode($this->data['death']),
            json_encode($this->data['sources']),
            json_encode($this->data['ids-in-sources']),
            json_encode($this->data['trust']),
            json_encode($this->data['acts']),
            json_encode($this->data['history']),
            json_encode($this->data['todo']),
            json_encode($this->data['notes']),
            $this->data['id'],
        ]);
    }
    
} // end class
