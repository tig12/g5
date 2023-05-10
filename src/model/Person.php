<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-12-27 01:37:08+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\model\wiki\BC;
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
        if(isset($row['ids_in_sources'])){
            $row['ids-in-sources'] = json_decode($row['ids_in_sources'], true);
            unset($row['ids_in_sources']);
        }
        if(isset($row['partial_ids'])){
            $row['partial-ids'] = json_decode($row['partial_ids'], true);
            unset($row['partial_ids']);
        }
        if(isset($row['name'])){
            $row['name'] = json_decode($row['name'], true);                             
        }
        if(isset($row['birth'])){
            $row['birth'] = json_decode($row['birth'], true);
        }
        if(isset($row['death'])){
            $row['death'] = json_decode($row['death'], true);
        }
        if(isset($row['occus'])){
            $row['occus'] = json_decode($row['occus'], true);
        }
        if(isset($row['acts'])){
            $row['acts'] = json_decode($row['acts'], true);
        }
        if(isset($row['history'])){
            $row['history'] = json_decode($row['history'], true);
        }
        if(isset($row['notes'])){
            $row['notes'] = json_decode($row['notes'], true);
        }
        $p = new Person();
        $p->data = $row;
        return $p;
    }
    
    // *********************** Create object of type Person *******************************
    
    /**
        Returns an object of type Person from storage, using its slug,
        or null if doesn't exist.
    **/
    public static function createFromSlug($slug): ?Person{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select * from person where slug=?');
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    //
    // ids_in_sources   related to raw input sources: 3a_sports (for ertel-sport), final3 (for cfepp)
    //
    
    /**
        Returns an object of type Person from storage,
        using its id for a given source,
        or null if doesn't exist.
        Ex : to get a person whose id in source a1 is 254, call
        createFromSourceId('a1', '254')
        @param  $source     Slug of the source
        @param  $idInSource Local id of the person within this source 
    **/
    public static function createFromSourceId($sourceSlug, $idInSource): ?Person {
        $dblink = DB5::getDbLink();
        $query = "select * from person where ids_in_sources @> '{\"$sourceSlug\": \"$idInSource\"}'";
        $stmt = $dblink->prepare($query);
        $stmt->execute([]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    //
    // partial_ids  related to higher level (parent) sources: lerrcp, muller, ertel
    //
    
    /**
        Returns one object of type Person from storage,
        
        using its id for a given source,
        or null if doesn't exist.
        Ex : to get a person whose id in source a1 is 254, call
        getBySourceId('lerrcp', 'A1-254')
        @param  $source     Slug of the source
        @param  $partialId partial id of the person within this source 
    **/
// WARNING - this function is not used anymore => remove ?
    public static function createFromPartialId($sourceSlug, $partialId): ?Person {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where partial_ids @> '{\"$sourceSlug\": \"$partialId\"}'");
        $stmt->execute([]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        return self::row2person($res);
    }
    
    /** 
        Returns an array of objects of type Person, related to a partial id.
        @param  $sourceSlug source slug used for partial id, like  g55, lerrcp, ertel, cfepp, 
    **/
    public static function createArrayFromPartialId($sourceSlug) {
        $dblink = DB5::getDbLink();
        $query = "select * from person where partial_ids->>'$sourceSlug'::text != 'null'";
        $res = [];
        foreach($dblink->query($query, \PDO::FETCH_ASSOC) as $row){
            $res[] = self::row2person($row);
        }
        return $res;
    }
    
    /**
        Returns an array of Persons with given occupation codes.
        @param $occus Array of occupation codes
    **/
    public static function createArrayFromOccus($occus){     
        $dblink = DB5::getDbLink();
        $query = 'select * from person where ';
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
    
    // *********************** Compute fields (static) *******************************
    
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
    
    // ***********************************************************************
    //                                  INSTANCE
    // ***********************************************************************
    
    /** 
        Replaces $this->data with fields present in $replace.
        Fields of $this->data not present in $replace are not modified.
        @param $replace Assoc. array with the same structure as $this->data
    **/
    public function updateFields($replace){
        $this->data = array_replace_recursive($this->data, $replace);
    }
    
    // *********************** Ids (instance) *******************************
    
    /** 
        Adds or updates a couple (source slug, id) in data['ids-in-sources']
    **/
    public function addIdInSource($sourceSlug, $id){
        $this->data['ids-in-sources'][$sourceSlug] = $id;
    }
    
    /** 
        Adds or updates a couple (source slug, id) in data['partial-ids']
    **/
    public function addPartialId($sourceSlug, $id){
        $this->data['partial-ids'][$sourceSlug] = $id;
    }
    
    // *********************** Slug (instance) *******************************
    
    /**
        @throws \Exception if the person id computation impossible (the person has no family name).
    **/
    public function getIdFromSlug($slug) {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('select id from person where slug=?');
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            throw new \Exception("Trying to get a person with unexisting slug : $slug");
        }
        return $res['id'];
    }
    
    /**
        Computes the slug of a person.
        Fame name has priority on regular name.
        ex :
            - galois-evariste-1811-10-25 for a person with a known birth time.
            - galois-evariste for a person without a known birth time.
        @throws \Exception if the person id computation impossible (the person has no family name).
        @see    static method doComputeSlug()
    **/
    public function computeSlug() {
        $name1 = $name2 = '';
        if($this->data['name']['fame']['full']){
            $name1 = $this->data['name']['fame']['full'];
        }
        else if($this->data['name']['fame']['family']){
            $name1 = $this->data['name']['fame']['family'];
            $name2 = $this->data['name']['fame']['given'];
        }
        else if($this->data['name']['family']){
            $name1 = $this->data['name']['family'];
            $name2 = $this->data['name']['given'];
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
    
    // *********************** Names (instance) *******************************
    
    /**
        Computes the family name, trying to find a non-empty value.
    **/
    public function familyName(): string {
        if($this->data['name']['family'] != ''){
            return $this->data['name']['family'];
        }
        if($this->data['name']['fame']['family'] != ''){
            return $this->data['name']['fame']['family'];
        }
        if($this->data['name']['fame']['full'] != ''){
            return $this->data['name']['fame']['full'];
        }
        return '';
    }
    
    /**
        Computes the given name, trying to find a non-empty value.
    **/
    public function givenName(): string {
        if($this->data['name']['given'] != ''){
            return $this->data['name']['given'];
        }
        if($this->data['name']['fame']['given'] != ''){
            return $this->data['name']['fame']['given'];
        }
        return '';
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
    
    // *********************** Occupations (instance) *******************************
    
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
        WARNING this function doesn't modify table person_groop.
                This must be done with Group::storePersonInGroup().
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
        // notes:
        // - array_values() permits to reindex
        // - if array_values() not used, jsonb is not stored as a regular array, but as an associative array.
        $this->data['occus'] = array_values(array_diff($occus, $remove));
    }
        
    // *********************** Other fields (instance) *******************************
    
    /**
        Computes the birth day from field birth.date or birth.date-ut.
        @return format 'YYYY-MM-DD' or ''
    **/
    public function birthday(): string {
        if(isset($this->data['birth']['date'])){
            return substr($this->data['birth']['date'], 0, 10);
        }
        else if(isset($this->data['birth']['date-ut'])){
            // for cura A
            return substr($this->data['birth']['date-ut'], 0, 10);
        }
        return '';
    }
    
    /**
        Computes the birth date (date = day + hour if any) from field birth.date or birth.date-ut.
        @return format f    'YYYY-MM-DD HH:MM' or 'YYYY-MM-DD' or ''
    **/
    public function birthdate(): string {
        if(isset($this->data['birth']['date'])){
            return $this->data['birth']['date'];
        }
        else if(isset($this->data['birth']['date-ut'])){
            // for cura A
            return $this->data['birth']['date-ut'];
        }
        return '';
    }
    
    /**
       Returns the history entry corresponding to a given source.
    **/
    public function historyFromSource($sourceSlug) {
        foreach($this->data['history'] as $hist){
            if($hist['source'] == $sourceSlug){
                return $hist;
            }
        }
        return null;
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
        Adds an array of notes
    **/
    public function addNotes($notes){
        foreach($notes as $note){
            if(!in_array($note, $this->data['notes'])){
                $this->data['notes'][] = $note;
            }
        }
    }
    
    // *********************** CRUD *******************************
    
    /**
        Inserts a new person in storage.
        Fills $this->data['id']
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('insert into person(
            slug,
            ids_in_sources,
            partial_ids,
            name,
            sex,
            birth,
            death,
            occus,
            trust,
            acts,
            history,
            notes
            )values(?,?,?,?,?,?,?,?,?,?,?,?) returning id');
        // JSON_FORCE_OBJECT => empty values are stored {} and not []
        $stmt->execute([
            $this->data['slug'],
            json_encode($this->data['ids-in-sources'], JSON_FORCE_OBJECT),
            json_encode($this->data['partial-ids'], JSON_FORCE_OBJECT),
            json_encode($this->data['name']), // not JSON_FORCE_OBJECT because of field alter, which is an array
            $this->data['sex'],
            json_encode($this->data['birth'], JSON_FORCE_OBJECT),
            json_encode($this->data['death'], JSON_FORCE_OBJECT),
            json_encode($this->data['occus']),
            $this->data['trust'],
            json_encode($this->data['acts'], JSON_FORCE_OBJECT),
            json_encode($this->data['history']),
            json_encode($this->data['notes']),
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        //
        $this->data['id'] = $res['id'];
        //
        return $res['id'];
    }

    /**
        Updates a person in storage.
        @throws \Exception if trying to update an unexisting id
    **/
    public function update() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare('update person set
            slug=?,
            ids_in_sources=?,
            partial_ids=?,
            name=?,
            sex=?,
            birth=?,
            death=?,
            occus=?,
            trust=?,
            acts=?,
            history=?,
            notes=?
            where id=?
            ');
        $stmt->execute([
            $this->data['slug'],
            json_encode($this->data['ids-in-sources'], JSON_FORCE_OBJECT),
            json_encode($this->data['partial-ids'], JSON_FORCE_OBJECT),
            json_encode($this->data['name']),
            $this->data['sex'],
            json_encode($this->data['birth'], JSON_FORCE_OBJECT),
            json_encode($this->data['death'], JSON_FORCE_OBJECT),
            json_encode($this->data['occus']),
            $this->data['trust'],
            json_encode($this->data['acts'], JSON_FORCE_OBJECT),
            json_encode($this->data['history']),
            json_encode($this->data['notes']),
            $this->data['id'],
        ]);
    }
    
} // end class
