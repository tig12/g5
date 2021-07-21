<?php
/******************************************************************************
    Regular array containing person paths (strings)
    ex of persons/1864/12/16/machin-pierre
    
    @license    GPL
    @history    2019-12-27 23:20:16+01:00, Thierry Graff : Creation
********************************************************************************/

namespace g5\model;

use g5\Config;                           
use g5\G5;

class Group{
    
    public $data = [];
    
    /** Boolean indicating if members have already been computed **/
    public $membersComputed;
    
    
    public function __construct(){
        $this->membersComputed = false;
        $this->data = yaml_parse(file_get_contents(__DIR__ . DS . 'Group.yml'));
    }
    
    // *********************** Get *******************************
    
    /**
        Creates an object of type Group from storage, using its id.
        Does not compute the members
    **/
    public static function get($id): ?Group{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from groop where id=?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $g = new Group();
        $g->data = $res;
        $g->data['members'] = [];
        return $g;
    }
    
    /**
        Creates an object of type Group from storage, using its slug.
        Does not compute the members
    **/
    public static function getBySlug($slug): ?Group{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from groop where slug=?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($res === false || count($res) == 0){
            return null;
        }
        $g = new Group();
        $g->data = $res;
        $g->data['members'] = [];
        return $g;
    }
    
    // *********************** CRUD *******************************
    
    /**
        Inserts a group in storage.
        @return The id of the inserted row
        @throws \Exception if trying to insert a duplicate slug
    **/
    public function insert(): int{
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into groop(slug,name,description) values(?,?,?) returning id");
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['description'],
        ]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->data['id'] = $res['id'];
        // members
        $this->insertMembers();
        //
        return $this->data['id'];
    }
    
    /**
        Inserts a the associations between a group and its members in storage (does not insert the persons).
    **/
    public function insertMembers() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("insert into person_groop(id_person,id_groop) values(?,?)");
        foreach($this->data['members'] as $pid){
            $stmt->execute([$pid, $this->data['id']]);
        }
    }
    
    /**
        Updates a group in storage.
        @throws \Exception if trying to update an unexisting group
    **/
    public function update() {
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("update groop set slug=?,name=?,description=? where id=?");
        $stmt->execute([
            $this->data['slug'],
            $this->data['name'],
            $this->data['description'],
            $this->data['id'],
        ]);
        $this->updateMembers();
    }
    
    /**
        Updates the associations between the group and its members in storage.
        @throws \Exception if trying to update an unexisting group
    **/
    public function updateMembers() {
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from person_groop where id_groop='" . $dblink->quote($this->data['id']) . "'");
        $stmt = $dblink->prepare("insert into person_groop(id_person,id_groop) values(?,?)");
        foreach($this->data['members'] as $pid){
            $stmt->execute([$pid, $this->data['id']]);
        }
    }
    
    /**
        Deletes the associations between group and persons (doesn't delete the persons).
        @throws \Exception if trying to delete members of an unexisting group
    **/
    public function deleteMembers() {
        $dblink = DB5::getDbLink();
        $dblink->exec("delete from person_groop where id_groop=" . $dblink->quote($this->data['id']));
        $this->data['members'] = [];
    }
    
    // *********************** Fields *******************************
    
    public function addMember($entry){
        $this->data['members'][] = $entry;
    }
    
    /** 
        Fills members with objects of type Person
        @param $force If true, members computation will be done even if it was already done.
    **/
    public function computeMembers(bool $force=false){
        if($force === false && $this->membersComputed === true){
            return;
        }
        $dblink = DB5::getDbLink();
        $stmt = $dblink->prepare("select * from person where id in(select id_person from person_groop where id_groop=?)");
        $stmt->execute([$this->data['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['members'] = [];
        foreach($rows as $row){
            $this->data['members'][] = Person::row2person($row);
            $this->membersComputed = true;
        }
    }
    
    // *********************** Export *******************************
    
    /** 
        Generates a csv from its members (of type Person)
            first line contains field names
            other lines contain data
        @param $csvFile     Path to the generated file
        @param $csvFields
            Names of the fields of the generated csv
            Are written in this order in the csv
            ex:
            $csvFields = ['GID', 'FNAME', 'GNAME', 'OCCU', '...', 'GEOID']
        @param $map
            Correspondance between person fields and csv fields
            For person fields, dot (.) is used to express multi-dimensional arrays
            For example, 'name.family' refers to $person['name']['family']
            Same syntax is used for regular arrays : 'occus.0' refers to $person[occus][0]
            ex:
            $map = [
                'name.family' => 'FNAME',
                'name.given' => 'GNAME',
                // ...
                'birth.place.geoid' => 'GEOID',
            ];
        
        @param $fmap Assoc array
                    key = field name in generated csv
                    value = function computing this field's value to write in the csv
                             parameter : a person
                             return : the value of the csv field
                    ex: 
                    $fmap = [
                        'OCCU' => function($p){
                            return implode('+', $p->data['occus']);
                        },
                    ];
        @param $sort    function used to sort the group's members.
                        if $sort = false, members are not sorted.
        @param $filters Regular array of functions returning a boolean.
                        If one of these functions returns false on a group member,
                        export() skips the record.
        @return Report
        
    **/
    public function exportCsv($csvFile, $csvFields, $map=[], $fmap=[], $sort=false, $filters=[]): string {
        
        $csv = implode(G5::CSV_SEP, $csvFields) . "\n";
        
        $emptyNew = array_fill_keys($csvFields, '');
        
        $this->computeMembers();
        
        if($sort !== false){
            usort($this->data['members'], $sort);
        }
        
        $report = '';

        $N = 0;
        foreach($this->data['members'] as $p){
            // filters
            foreach($filters as $function){
                if(!$function($p)){
                    continue 2;
                }
            }
            $new = $emptyNew;
            // map
            foreach($map as $personKey => $csvKey){          
                $pks = explode('.', $personKey);
                $data = null;
                foreach($pks as $pk){
                    $data = is_null($data) ? $p->data[$pk] : $data[$pk];
                }
                $new[$csvKey] = $data;
            }
            // fmap
            foreach($fmap as $csvKey => $function){
                $new[$csvKey] = $function($p);
            }
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $N++;
        }
        
        $dir = dirname($csvFile);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
            $report .= "Created directory $dir\n";
        }
        file_put_contents($csvFile, $csv);
        $report .= "Generated $N lines in file $csvFile\n";
        return $report;
    }
    
}// end class
